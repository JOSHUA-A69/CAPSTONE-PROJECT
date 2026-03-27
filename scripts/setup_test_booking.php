<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Creating Test Organization Bookings ===\n\n";

    // Get the requestor (test user)
    $stmt = $db->query("SELECT id FROM users WHERE role = 'requestor' LIMIT 1");
    $requestor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$requestor) {
        echo "No requestor found\n";
        exit(1);
    }

    // Create a test booking
    $stmt = $db->prepare("INSERT INTO organization_booking_requests (
        requestor_id, organization_id, activity_name, purpose,
        requested_date, requested_venue, estimated_participants,
        servers_needed, status, submitted_at, adviser_notified_at, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $now = date('Y-m-d H:i:s');
    $dateTime = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Create booking with multiple organizations
    $stmt->execute([
        $requestor['id'],
        1, // primary org
        'Youth Fellowship Activity',
        'Monthly gathering for youth group',
        $dateTime,
        'Main Auditorium',
        50,
        2,
        'pending',
        $now,
        null,
        $now,
        $now
    ]);

    $bookingId = $db->lastInsertId();
    echo "✓ Created booking ID: $bookingId\n";

    // Attach multiple organizations
    $orgs = [1, 2, 3];
    $stmt = $db->prepare("INSERT INTO organization_booking_organizations (
        booking_request_id, organization_id, is_primary, notified, notified_at,
        approval_status, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($orgs as $idx => $orgId) {
        $stmt->execute([
            $bookingId,
            $orgId,
            ($idx === 0) ? 1 : 0,  // is_primary
            1,  // notified = true (simulating notification was sent)
            $now,  // notified_at =now
            'pending',  // approval_status
            $now,
            $now
        ]);
        echo "  - Attached to organization ID $orgId\n";
    }

    echo "\n✓ Test booking created with multiple organizations!\n";
    echo "\n=== Verification ===\n\n";

    // Verify bookings exist
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM organization_booking_requests");
    $stmt->execute();
    $bookingCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "✓ Total bookings in system: $bookingCount\n";

    // Verify pivot table entries
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM organization_booking_organizations WHERE booking_request_id = ?");
    $stmt->execute([$bookingId]);
    $orgCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "✓ Organizations attached to booking: $orgCount\n";

    // Check if adviser can see it with fixed query
    $stmt = $db->prepare("
        SELECT COUNT(*) as cnt FROM organization_booking_requests obr
        WHERE obr.status = 'pending' AND obr.id IN (
            SELECT booking_request_id FROM organization_booking_organizations
            WHERE organization_id IN (1, 2, 3)
        )
    ");
    $stmt->execute();
    $adviserCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "✓ Bookings visible to adviser Cecilia: $adviserCount\n";

    echo "\n✓ TEST COMPLETE!\n";
    echo "\nNow test the adviser dashboard by:\n";
    echo "1. Log in as adviser: cecilia.adviser@example.com\n";
    echo "2. Go to Dashboard\n";
    echo "3. Look for 'Organization Booking Services' section\n";
    echo "4. Should see 1 pending booking with 3 organizations\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
?>
