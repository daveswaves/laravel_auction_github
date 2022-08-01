@extends('layout')

@section('content')
    <!-- resources/views/sellers.blade.php -->
    <table class="table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>NAME/ADDRESS</th>
                <th>Commission</th>
                <th>Carriage</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sellers as $seller)
            <tr>
                <td>{{ $seller['id'] }}</td>
                <td>{{ $seller['name_address'] }} <a class="btn float_right" href="lots/{{ $seller['id'] }}">lots</a></td>
                <td>{{ $seller['commission'] }}%</td>
                <td>{!! $seller['carriage'] !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection