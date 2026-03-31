@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="height: 60vh;">
    <div class="card shadow-lg p-4 text-center" style="width: 320px; border-radius: 15px;">
        <h5 class="mb-3 fw-bold">Maintenance Mode</h5>

        <div class="form-check form-switch d-flex justify-content-center">
            <input class="form-check-input" type="checkbox" id="maintenanceToggle"
                {{ isset($maintenance) && $maintenance->status == '1' ? 'checked' : '' }}
                style="transform: scale(1.8); cursor:pointer;">
        </div>

        <p class="mt-3 fw-semibold" id="statusText">
            {{ isset($maintenance) && $maintenance->status == '1' ? 'ON' : 'OFF' }}
        </p>
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
                // Backend API Call
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
                        statusLabel.innerText = isChecked ? 'ON' : 'OFF';
                        Swal.fire("Updated!", data.message, "success");
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    // Error hone par checkbox wapas reset karein
                    this.checked = !isChecked;
                    Swal.fire("Error", "Failed to update: " + error.message, "error");
                });
            } else {
                // Cancel karne par toggle purani state mein
                this.checked = !isChecked;
            }
        });
    });
</script>
@endsection