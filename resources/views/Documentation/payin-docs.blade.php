@extends('layouts.app')

@section('title', 'Payin API Documentation')
@section('page-title', 'Payin API Documentation')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        <form>

            <div class="row">

                <!-- Header -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Header</label>
                    <textarea id="editor1" class="form-control editor"></textarea>
                </div>

                <!-- Authorization -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Authorization</label>
                    <textarea id="editor2" class="form-control editor"></textarea>
                </div>

                <!-- Generate Payment Response -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Generate Payment Response</label>
                    <textarea id="editor3" class="form-control editor"></textarea>
                </div>

                <!-- Generate Payment Description -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Generate Payment Description</label>
                    <textarea id="editor4" class="form-control editor"></textarea>
                </div>

                <!-- Check Status Response -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Check Status Response</label>
                    <textarea id="editor5" class="form-control editor"></textarea>
                </div>

                <!-- Check Status Description -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Check Status Description</label>
                    <textarea id="editor6" class="form-control editor"></textarea>
                </div>

                <!-- Callback Example Response -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Callback Example Response</label>
                    <textarea id="editor7" class="form-control editor"></textarea>
                </div>

                <!-- Callback Example Description -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Callback Example Description</label>
                    <textarea id="editor8" class="form-control editor"></textarea>
                </div>

            </div>

            <div class="text-end">
                <button type="button" class="btn btn-primary">Save (UI Only)</button>
            </div>

        </form>

    </div>
</div>


<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>

<script>
    CKEDITOR.replace('editor1');
    CKEDITOR.replace('editor2');
    CKEDITOR.replace('editor3');
    CKEDITOR.replace('editor4');
    CKEDITOR.replace('editor5');
    CKEDITOR.replace('editor6');
    CKEDITOR.replace('editor7');
    CKEDITOR.replace('editor8');
</script>
@endsection

