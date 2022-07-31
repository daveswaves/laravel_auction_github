<!-- resources/views/sellers.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Auction DB</title>

<style>
    * {
        font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
        font-size: 14px;
        line-height: 1.42857143;
        color: #333;
    }
    
    .container {
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
        
        width: 1170px;
    }
    
    table {
        border-spacing: 0;
        border-collapse: collapse;
    }
    .table-bordered {
        border: 1px solid #ddd;
    }
    
    thead>tr {
        background: #5bc0de;
    }
    thead>tr>th, tbody>tr>td {
        border: 1px solid #ddd;
        padding: 5px;
    }
    thead>tr>th:nth-child(1) { width: 6%; }
    thead>tr>th:nth-child(2) { width: 80%; }
    
    tbody>tr:nth-of-type(even){
        background: #c4e3f3;
    }
    tbody>tr:nth-of-type(odd){
        background: #d9edf7;
    }
    tbody>tr:hover {
        background: #b3d2e2;
    }
    
    .float_right {
        float: right;
    }
    
    .btn {
        color: #fff;
        background-color: #337ab7;
        border-color: #2e6da4;
        
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        text-decoration: none;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    .btn:hover {
        background-color: #286090;
        border-color: #204d74;
    }
</style>

<!-- 
.style-tbl tr:nth-child(2n+2){ background: rgb(228, 238, 250); } /* light blue */
.style-tbl tr:first-child{ background: rgb(238, 238, 238); } /* light grey */
 -->

</head>
<body>

<div class="container">
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
</div>


</body>
</html>