@extends('layouts.app')

@section('title', 'Payin API Documentation')
@section('page-title', 'Payin API Documentation')

@section('content')

<style>
    /* FIXED HEIGHT WRAPPER (IMPORTANT) */
    .quill-wrapper {
        /* height: 180px; */
        border-radius: 6px;
        background: #fff;
    }

    .quill-editor {
        height: 100%;
    }

    .ql-container {
        min-height: 120px;
        overflow: hidden;
    }

    .ql-editor {
        height: 140px;
        overflow-y: auto;
    }
</style>

<div class="card shadow-sm p-4">
    <div class="card-body">

        <form method="POST" action="{{route('save_payin_documentation')}}" id="apiDocForm">
            @csrf

            <div class="row g-4">

                <!-- Header -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Header</label>
                    <input type="hidden" name="header" id="input_editor1">

                    <div class="quill-wrapper">
                        <div id="editor1" class="quill-editor"></div>
                        @error('header')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Authorization -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Authorization</label>

                    <input type="hidden" name="authorization" id="input_editor2">

                    <div class="quill-wrapper">
                        <div id="editor2" class="quill-editor"></div>
                        @error('authorization')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Generate Payment Response -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Generate Payment Response</label>
                    <input type="hidden" name="generate_payment_response" id="input_editor3">

                    <div class="quill-wrapper">
                        <div id="editor3" class="quill-editor"></div>
                        @error('generate_payment_response')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Generate Payment Description -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Generate Payment Description</label>
                    <input type="hidden" name="generate_payment_description" id="input_editor4">

                    <div class="quill-wrapper">
                        <div id="editor4" class="quill-editor"></div>
                        @error('generate_payment_description')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Check Status Response -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Check Status Response</label>
                    <input type="hidden" name="check_status_response" id="input_editor5">

                    <div class="quill-wrapper">
                        <div id="editor5" class="quill-editor"></div>
                        @error('check_status_response')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Check Status Description -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Check Status Description</label>
                    <input type="hidden" name="check_status_description" id="input_editor6">

                    <div class="quill-wrapper">
                        <div id="editor6" class="quill-editor"></div>
                        @error('check_status_description')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Callback Example Response -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Callback Example Response</label>
                    <input type="hidden" name="callback_example_response" id="input_editor7">

                    <div class="quill-wrapper">
                        <div id="editor7" class="quill-editor"></div>
                        @error('callback_example_response')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <!-- Callback Example Description -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Callback Example Description</label>
                    <input type="hidden" name="callback_example_description" id="input_editor8">

                    <div class="quill-wrapper">
                        <div id="editor8" class="quill-editor"></div>
                        @error('callback_example_description')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn buttonColor">
                        Save
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {

        const quillEditors = {};

        // INIT QUILL (EARLY BUT SAFE)
        document.querySelectorAll('.quill-editor').forEach(el => {

            const editor = new Quill('#' + el.id, {
                theme: 'snow'
            });

            quillEditors[el.id] = editor;
        });

        // WAIT A BIT FOR SAFE RENDER (IMPORTANT FIX)
        setTimeout(() => {

            const oldData = {
                editor1: `{!! old('header', $data->request_header ?? '') !!}`,
                editor2: `{!! old('authorization', $data->authorization ?? '') !!}`,
                editor3: `{!! old('generate_payment_response', $data->generate_payment_response ?? '') !!}`,
                editor4: `{!! old('generate_payment_description', $data->generate_payment_description ?? '') !!}`,
                editor5: `{!! old('check_status_response', $data->check_status_response ?? '') !!}`,
                editor6: `{!! old('check_status_description', $data->check_status_description ?? '') !!}`,
                editor7: `{!! old('callback_example_response', $data->callback_examples_response ?? '') !!}`,
                editor8: `{!! old('callback_example_description', $data->callback_examples_description ?? '') !!}`,
            };

            Object.keys(oldData).forEach(id => {
                if (quillEditors[id]) {
                    quillEditors[id].root.innerHTML = oldData[id] || '';
                }
            });

        }, 100);

        // SAFE SUBMIT HANDLER (CRITICAL FIX)
        document.getElementById('apiDocForm').addEventListener('submit', function () {
            for (let id in quillEditors) {
                const editor = quillEditors[id];
                const input = document.getElementById('input_' + id);

                if (editor && input) {
                    input.value = editor.root.innerHTML || '';
                }

            }

        });

    });
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}"
    });
</script>
@endif



@endsection