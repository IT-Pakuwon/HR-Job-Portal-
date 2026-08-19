@extends('emails.layouts.master')

@section('title', 'Event Reminder H-7')

@section('icon', '📅')

@section('header', 'Upcoming Paid Event — 7 Days To Go')

@section('subtitle')
This event starts in 7 days and is marked as Paid.
@endsection

@section('content')
@include('emails.partials.event-h7-detail')
@endsection
