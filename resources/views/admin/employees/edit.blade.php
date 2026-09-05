@extends('layouts.admin')

@section('title', 'Edit Employee - ' . $siteName)

@section('content')
<style>
    @media (max-width: 767.98px) {
        .employee-form-card {
            border-radius: 14px !important;
        }
        .page-header-title {
            font-size: 1.25rem !important;
        }
        .page-header-subtitle {
            font-size: 0.75rem !important;
        }
        .back-btn-mobile {
            padding: 0.35rem 0.75rem !important;
            font-size: 0.78rem !important;
        }
        .form-section-title {
            font-size: 0.88rem !important;
        }
        .form-label {
            font-size: 0.78rem !important;
            margin-bottom: 0.25rem !important;
        }
        .form-control, .form-select, .input-group-text {
            font-size: 0.82rem !important;
            padding: 0.45rem 0.65rem !important;
        }
        .submit-action-btn {
            width: 100% !important;
            padding: 0.65rem !important;
            font-size: 0.88rem !important;
        }
        .cancel-action-btn {
            width: 100% !important;
            padding: 0.55rem !important;
            font-size: 0.82rem !important;
        }
        .form-actions-container {
            flex-direction: column-reverse !important;
            gap: 0.5rem !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Header Row -->
    <div class="d-flex align-items-center justify-content-between mb-3 mb-md-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 page-header-title">
                <i class="fa-solid fa-user-pen text-info me-2"></i>Edit Employee Profile
            </h4>
            <p class="text-muted small mb-0 page-header-subtitle">Update employee details and salary structure.</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-sm back-btn-mobile">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- Main Card Container -->
    <div class="card border-0 shadow-sm rounded-4 employee-form-card mx-auto" style="max-width: 780px;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" onsubmit="return handleAdminFormSubmit(this);">
                @csrf
                @method('PUT')

                <!-- Section 1: Basic Information -->
                <div class="mb-3 mb-md-4 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-3 form-section-title">
                        <i class="fa-solid fa-id-card text-primary me-2"></i>Personal & Contact Details
                    </h6>

                    <div class="row g-2.5 g-md-3">
                        <!-- Employee Name -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Employee Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                            </div>
                            @error('name') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>

                        <!-- Designation -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Designation / Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-briefcase"></i></span>
                                <input type="text" name="designation" class="form-control border-start-0 @error('designation') is-invalid @enderror" value="{{ old('designation', $employee->designation) }}">
                            </div>
                            @error('designation') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" name="phone" class="form-control border-start-0 @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            @error('phone') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                            </div>
                            @error('email') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Salary Structure -->
                <div class="p-3 p-md-3.5 bg-light rounded-3 border mb-3 mb-md-4">
                    <h6 class="fw-bold text-dark mb-3 form-section-title">
                        <i class="fa-solid fa-calculator text-warning me-2"></i>Salary Structure
                    </h6>

                    <div class="row g-2.5 g-md-3">
                        <!-- Salary Type -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Salary Type <span class="text-danger">*</span></label>
                            <select name="salary_type" id="salaryTypeSelect" class="form-select rounded-3 @error('salary_type') is-invalid @enderror" required>
                                <option value="fixed" {{ old('salary_type', $employee->salary_type) === 'fixed' ? 'selected' : '' }}>Fixed Salary (Monthly Fixed Amount)</option>
                                <option value="non_fixed" {{ old('salary_type', $employee->salary_type) === 'non_fixed' ? 'selected' : '' }}>Non-Fixed Salary (Variable / Daily Basis)</option>
                            </select>
                            @error('salary_type') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>

                        <!-- Monthly Salary Input -->
                        <div class="col-12 col-md-6" id="monthlySalaryContainer">
                            <label class="form-label fw-bold text-dark">Monthly Salary (₹) <span class="text-danger" id="monthlySalaryReqMark">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-dark">₹</span>
                                <input type="number" step="0.01" min="0" name="monthly_salary" id="monthlySalaryInput" class="form-control border-start-0 @error('monthly_salary') is-invalid @enderror" value="{{ old('monthly_salary', $employee->monthly_salary) }}">
                            </div>
                            @error('monthly_salary') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                            <div class="form-text small text-muted" style="font-size: 0.72rem;">Required for Fixed Salary. Leave empty for Non-Fixed.</div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Joining & Additional Info -->
                <div class="mb-3 mb-md-4">
                    <h6 class="fw-bold text-dark mb-3 form-section-title">
                        <i class="fa-solid fa-calendar-days text-success me-2"></i>Employment Details
                    </h6>

                    <div class="row g-2.5 g-md-3">
                        <!-- Joining Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Joining Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-calendar"></i></span>
                                <input type="date" name="joining_date" class="form-control border-start-0 @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}">
                            </div>
                            @error('joining_date') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>

                        <!-- Notes -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Notes / Internal Remarks</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-note-sticky"></i></span>
                                <input type="text" name="notes" class="form-control border-start-0 @error('notes') is-invalid @enderror" value="{{ old('notes', $employee->notes) }}" placeholder="e.g. Working hours 9 AM - 7 PM">
                            </div>
                            @error('notes') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top form-actions-container">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary rounded-3 px-4 py-2 fw-semibold cancel-action-btn">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-dark text-white rounded-3 px-4 py-2 fw-bold shadow-sm submit-action-btn">
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
