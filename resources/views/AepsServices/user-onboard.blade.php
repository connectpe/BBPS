@extends('layouts.app')

@section('title', 'AEPS User Onboard')
@section('page-title', 'AEPS User Onboard')

@section('content')

    @php
        $business = $user->business;
        $bank = $user->bankDetails;

        $name = explode(' ', trim($user->name ?? ''));

        $firstName = $name[0] ?? '';
        $middleName = count($name) > 2 ? $name[1] : '';
        $lastName = count($name) > 1 ? end($name) : '';
    @endphp

    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">AEPS User Onboard Form</h5>
            </div>

            <div class="card-body">

                <form method="POST" action="" enctype="multipart/form-data">
                    @csrf

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="merchantFName"
                                value="{{ old('merchantFName', $firstName) }}" placeholder="Enter First Name">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="merchantMName"
                                value="{{ old('merchantMName', $middleName) }}" placeholder="Enter Middle Name">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="merchantLName"
                                value="{{ old('merchantLName', $lastName) }}" placeholder="Enter Last Name">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" maxlength="10" name="merchantPhoneNumber"
                                value="{{ old('merchantPhoneNumber', $user->mobile ?? '') }}" placeholder="9876543210">
                        </div>



                        <div class="col-md-4 mb-3">
                            <label class="form-label">Aadhaar Number</label>
                            <input type="text" class="form-control" maxlength="12" name="merchantAadhar"
                                value="{{ old('merchantAadhar', $business->aadhar_number ?? '') }}"
                                placeholder="123412341234">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">PAN Number</label>
                            <input type="text" class="form-control" name="userPan"
                                value="{{ old('userPan', $business->pan_number ?? '') }}" placeholder="ABCDE1234F">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Company Type</label>

                            <select class="form-select" name="companyType">

                                <option value="">--Select Company Type--</option>
                                @foreach ($companyType as $type)
                                    <option value="{{ $type['mccDescription'] }}"
                                        {{ old('companyType') == $type['mccDescription'] ? 'selected' : '' }}>
                                        {{ $type['mccDescription'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control" name="companyBankAccountNumber"
                                value="{{ old('companyBankAccountNumber', $bank->account_number ?? '') }}"
                                placeholder="Enter Account Number">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank IFSC Code</label>
                            <input type="text" class="form-control" name="bankIfscCode"
                                value="{{ old('bankIfscCode', $bank->ifsc_code ?? '') }}" placeholder="ICIC0000001">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Upload Aadhaar</label>
                            <input type="file" class="form-control" name="aadharPics">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Upload PAN Card</label>
                            <input type="file" class="form-control" name="pancardPics">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Upload Passport Photo</label>
                            <input type="file" class="form-control" name="passports">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Upload Shop Photo</label>
                            <input type="file" class="form-control" name="shoppics">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="merchantState"
                                value="{{ old('merchantState', $business->state ?? '') }}" placeholder="Enter State">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="merchantCityName"
                                value="{{ old('merchantCityName', $business->city ?? '') }}" placeholder="Enter City">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">District</label>
                            <input type="text" class="form-control" name="merchantDistrictName"
                                value="{{ old('merchantDistrictName') }}" placeholder="Enter District">
                        </div>


                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pin Code</label>
                            <input type="text" class="form-control" maxlength="6" name="merchantPinCode"
                                value="{{ old('merchantPinCode', $business->pincode ?? '') }}" placeholder="201301">
                        </div>


                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="3" name="merchantAddress" placeholder="Enter Address">{{ old('merchantAddress', $business->address ?? '') }}</textarea>
                        </div>

                    </div>

                    <div class="text-end mt-3">
                        <button type="reset" class="btn btn-secondary">
                            Reset
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>

@endsection
