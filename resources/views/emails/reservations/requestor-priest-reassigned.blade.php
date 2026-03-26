@extends('emails.layouts.default')

@section('title', 'Priest Reassignment')
@section('heading', 'Priest Reassignment')

@section('content')
    <p style="margin-bottom: 20px;">Hello {{ $requestorName }},</p>

    <p style="margin-bottom: 20px;">
        There has been a change in priest assignment for your **{{ $reservation->service->service_name }}** reservation on {{ $reservation->schedule_date->format('F d, Y \a\t h:i A') }}.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <div style="margin-bottom: 15px;">
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Previous Priest:</p>
            <p style="margin: 5px 0 0 0; color: #9ca3af; text-decoration: line-through;">{{ $oldPriestName }}</p>
        </div>
        
        <div>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">New Priest:</p>
            <p style="margin: 5px 0 0 0; color: #111827; font-weight: 600; font-size: 18px;">{{ $newPriestName }}</p>
        </div>
    </div>

    <p style="margin-bottom: 20px;">
        The new priest will need to confirm their availability. You will be notified once confirmed.
    </p>
@endsection
