@extends('emails.layouts.master')

@section('title', 'Ticket Reopened')

@section('icon', '🔄')

@section('header', 'Ticket Reopened')

@section('subtitle')
This ticket has been reopened and requires further action.
@endsection

@section('content')

@include('emails.partials.ticket-detail')

@endsection
