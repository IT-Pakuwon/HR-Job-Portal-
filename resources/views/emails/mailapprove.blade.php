@extends('emails.layouts.system')

@section('title', 'Waiting Approval')
@section('preheader', ($info ?? 'A request') . ' from ' . ($name ?? '') . ' needs your approval.')
@section('icon', '🗂️')
@section('header', 'Waiting Your Approval')
@section('subtitle', $info ?? 'Approval Request')

@section('content')
    <p style="margin:0 0 4px;">Hi,</p>
    <p style="margin:0 0 4px;">A request is waiting for your approval.</p>

    @include('emails.partials.detail-table', ['rows' => [
        ['label' => 'Name', 'value' => $name],
        ['label' => 'Company', 'value' => $cpnyid],
        ['label' => 'Department', 'value' => $deptname],
        ['label' => 'Date', 'value' => $date],
    ]])

    @include('emails.partials.button', ['url' => $url, 'label' => 'Review Request'])
@endsection
