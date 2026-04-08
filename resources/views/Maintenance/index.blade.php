@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="height: 70vh; background: #f4f6f9;">
    
    <div class="card border-0 shadow-lg text-center p-4"
        style="width: 360px; border-radius: 20px; background: #ffffff;">

        {{-- Icon --}}
        <div class="mb-3">
            <i class="fa fa-tools" style="font-size: 40px; color: #6c757d;"></i>
        </div>

        {{-- Title --}}
        <h5 class="fw-bold mb-2">Maintenance Mode</h5>
        <p class="text-muted small mb-4">Enable or disable system maintenance</p>

        {{-- Toggle  --}}
        <div class="form-check form-switch d-flex justify-content-center mb-3">
            <input class="form-check-input" type="checkbox" id="maintenanceToggle"
                {{ isset($maintenance) && $maintenance->status == '1' ? 'checked' : '' }}
                style="transform: scale(1.6); cursor:pointer;">
        </div>

        {{-- Status Badge --}}
        <span id="statusText"
            class="badge px-3 py-2"
            style="font-size: 14px; border-radius: 20px;
            background: {{ isset($maintenance) && $maintenance->status == '1' ? '#dc3545' : '#28a745' }};
            color: #fff;">
            {{ isset($maintenance) && $maintenance->status == '1' ? 'ON' : 'OFF' }}
        </span>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('maintenanceToggle').addEventListener('change', function () {
    let isChecked = this.checked;
    let newStatus = isChecked ? '1' : '0';
    let statusLabel = document.getElementById('statusText');

    Swal.fire({
        title: "Are you sure?",
        text: isChecked ? "Enable Maintenance Mode?" : "Disable Maintenance Mode?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes"
    }).then((result) => {
        if (result.isConfirmed) {

            fetch("{{ route('update_maintenance_mode') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {

                    // Update Badge Text + Color
                    statusLabel.innerText = isChecked ? 'ON' : 'OFF';
                    statusLabel.style.background = isChecked ? '#dc3545' : '#28a745';

                    Swal.fire("Updated!", data.message, "success");
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                this.checked = !isChecked;
                Swal.fire("Error", "Failed to update: " + error.message, "error");
            });

        } else {
            this.checked = !isChecked;
        }
    });
});
</script>
@endsection