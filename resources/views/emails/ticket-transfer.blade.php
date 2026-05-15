@extends('emails.layouts.master')

@section('title', 'Ticket Transfer')

@section('icon', '📤')

@section('header', 'Ticket Transferred')

@section('subtitle')
A ticket has been transferred to another PIC.
@endsection

@section('content')

@include('emails.partials.ticket-detail')

@endsection
