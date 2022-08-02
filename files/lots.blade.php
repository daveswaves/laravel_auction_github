@extends('layout')

@section('content')
    <table class="table-bordered full-width mb-30px">
        <thead>
            <tr>
                <th>ID</th>
                <th>NAME/ADDRESS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $seller['id'] }}</td>
                <td>{!! $seller['name_address'] !!}</td>
            </tr>
        </tbody>
    </table>

    <table class="table-bordered full-width">
        <thead>
            <tr>
                <th>NO.</th>
                <th>LOT NAME</th>
                <th>W/D</th>
                <th>ELEC</th>
                <th>PRICE(&pound;)</th>
            </tr>
        </thead>
        <tbody>
        @foreach($lots as $lot)
            <tr>
                <td>{{ $lot['lot_no'] }}</td>
                <td>{!! $lot['lot_name'] !!}</td>
                <td>{!! $lot['withdrawn_cbx'] !!}</td>
                <td>{!! $lot['electric_dropdown'] !!}</td>
                <td>{!! $lot['lot_price_txtbx'] !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection