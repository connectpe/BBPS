@extends('layouts.app')

@section('title', 'Complaint Status')
@section('page-title', 'Complaint Status')

@section('content')

<div class="container">

    <div class="row g-4">

        <!-- Search Form -->
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Check Complaint Status</h5>
                </div>
                <div class="card-body">
                    <form id="transactionForm">
                        <div class="row g-3 align-items-end">

                            @php
                            $complaintTypes = [
                            'Complaint Type 1',
                            'Complaint Type 2',
                            'Complaint Type 3',
                            ];
                            @endphp

                            <div class="col-4 col-md-4">
                                <label for="reason" class="form-label">Complaint Type</label>
                                <select name="reason" id="reason" class="form-control">
                                    <option value="">--Complaint Type--</option>
                                    @foreach($complaintTypes as $type)
                                    <option value="{{$type}}">{{$type}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-4 col-md-4">
                                <label for="services" class="form-label">Complaint ID</label>
                                <input type="text" id="services" class="form-control" placeholder="Complaint ID">
                            </div>

                            <!-- Row 3: Search Button -->
                            <div class="col-4 col-md-4">
                                <button type="submit" class="btn buttonColor w-100">
                                    Check Status
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Transaction Result -->
        <div class="col-12" id="resultArea">

        </div>
    </div>
</div>

<script>
    // Handle form submit
    $('#transactionForm').on('submit', function(e) {
        e.preventDefault();
        let html = `
           <div class="card border shadow-sm">
           <div class="card-header"> <h5>Complaint Status</h5></div>
            <div class="card-body"> 
           <div class="table-responsive">
             <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Complaint ID</th>
                        <th>Complaint Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>${'MocktACeq7X4uL'}</td>
                        <td>${'ASSIGNED'}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
        </div>
        `;

        $('#resultArea').html(html);
    });
</script>

@endsection