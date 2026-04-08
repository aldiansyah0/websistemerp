@extends('layouts.app')

@section('content')
    <livewire:operations.purchase-order-form-panel :purchase-order-id="$purchaseOrderId" />
@endsection
