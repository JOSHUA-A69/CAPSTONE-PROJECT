@extends('emails.layouts.default')

@section('title', 'Report Empty')
@section('heading', 'No Data Found')

@section('content')
    <div style="background-color: #fffbeb; color: #92400e; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fcd34d; margin-bottom: 25px;">
        <strong>Note:</strong> No records matched your criteria.
    </div>

    <p style="margin-bottom: 20px;">Hello,</p>

    <p style="margin-bottom: 20px;">We processed your report request, but unfortunately, no data was found that matches the selected filters.</p>

    <div style="background-color: #f9fafb; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px;">
        <strong style="color: #b45309;">Why is this happening?</strong>
        <p style="margin: 5px 0 0 0; font-size: 14px; color: #4b5563;">
            This usually occurs when the date range is too narrow or specific filters exclude all available records.
        </p>
    </div>

    <p><strong>Suggestions:</strong></p>
    <ul style="color: #4b5563; margin-top: 5px;">
        <li style="margin-bottom: 5px;">Try expanding your date range.</li>
        <li style="margin-bottom: 5px;">Remove or relax some filters.</li>
        <li style="margin-bottom: 5px;">Check back later when more data might be available.</li>
    </ul>
@endsection
