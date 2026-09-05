@extends('layouts.admin')

@section('title', 'Edit Employee - ' . $siteName)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-user-pen text-info me-2"></i> Edit Employee Profile</h4>
            <p class="text-muted small mb-0">Update employee details and salary structure.</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 max-w-3xl mx-auto">
        <div class="card-body p-4">
            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" onsubmit="return handleAdminFormSubmit(this);">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-dark">Employee Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-dark">Designation / Role</label>
                        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $employee->designation) }}">
                        @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-dark">Phone Number</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-dark">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Salary Configuration -->
                <div class="bg-light p-3 rounded-4 mb-3">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calculator text-warning me-2"></i> Salary Structure</h6>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark">Salary Type <span class="text-danger">*</span></label>
                            <select name="salary_type" id="salaryTypeSelect" class="form-select @error('salary_type') is-invalid @enderror" required>
                                <option value="fixed" {{ old('salary_type', $employee->salary_type) === 'fixed' ? 'selected' : '' }}>Fixed Salary (Monthly Amount Required)</option>
                                <option value="non_fixed" {{ old('salary_type', $employee->salary_type) === 'non_fixed' ? 'selected' : '' }}>Non-Fixed Salary (Variable / Daily Work)</option>
                            </select>
                            @error('salary_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6" id="monthlySalaryContainer">
                            <label class="form-label fw-semibold text-dark">Monthly Salary (₹) <span class="text-danger" id="monthlySalaryReqMark">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="0.01" min="0" name="monthly_salary" id="monthlySalaryInput" class="form-control @error('monthly_salary') is-invalid @enderror" value="{{ old('monthly_salary', $employee->monthly_salary) }}">
                            </div>
                            @error('monthly_salary') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-dark">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}">
                        @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-dark">Notes / Internal Remarks</label>
                        <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes', $employee->notes) }}">
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1 text-warning"></i> Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const salaryTypeSelect = document.getElementById('salaryTypeSelect');
        const monthlySalaryContainer = document.getElementById('monthlySalaryContainer');
        const monthlySalaryInput = document.getElementById('monthlySalaryInput');
        const monthlySalaryReqMark = document.getElementById('monthlySalaryReqMark');

        function toggleSalaryFields() {
            if (salaryTypeSelect.value === 'fixed') {
                monthlySalaryContainer.style.display = 'block';
                monthlySalaryInput.required = true;
                monthlySalaryReqMark.style.display = 'inline';
            } else {
                monthlySalaryContainer.style.display = 'none';
                monthlySalaryInput.required = false;
                monthlySalaryInput.value = '';
                monthlySalaryReqMark.style.display = 'none';
            }
        }

        salaryTypeSelect.addEventListener('change', toggleSalaryFields);
        toggleSalaryFields();
    });
</script>
@endsection
