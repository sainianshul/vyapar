@extends('admin.layouts.app')

@section('title', 'Support Categories')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Support', 'url' => route('admin.support.index')],
                    ['label' => 'Categories']
                ]" />
                <h2 class="page-title">Support Categories</h2>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_category">
                    <i class="ti ti-plus me-1"></i> Add Category
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        {{-- Toolbar --}}
        <div class="card-header d-flex justify-content-between align-items-center py-3 border-bottom">
            <div class="d-flex gap-2">
                <div class="input-icon" style="width: 250px;">
                    <span class="input-icon-addon">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" id="dt-search" class="form-control" placeholder="Search categories...">
                </div>
            </div>
        </div>
        
        {{-- Body --}}
        <div class="card-body">
            <div class="table-responsive">
                <table id="kt_categories_table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Name</th>
                            <th class="text-center">Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-body fw-medium">{{ $category->name }}</td>
                                <td class="text-center">
                                    @if($category->status)
                                        <span class="badge badge-outline text-success border-success fs-9 px-2 py-1">Active</span>
                                    @else
                                        <span class="badge badge-outline text-danger border-danger fs-9 px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    {{ $category->created_at ? $category->created_at->format('d M Y, h:i A') : 'N/A' }}
                                </td>
                                <td class="text-secondary">
                                    {{ $category->updated_at ? $category->updated_at->format('d M Y, h:i A') : 'N/A' }}
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-list flex-nowrap justify-content-end">
                                        <button class="btn btn-icon btn-outline-warning btn-sm" 
                                                onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->status }})"
                                                data-bs-toggle="tooltip" title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        
                                        <form action="{{ route('admin.support.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? (Soft Delete will be applied)');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal modal-blur fade" id="kt_modal_add_category" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.support.categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Support Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Category Name</label>
                            <input type="text" class="form-control" placeholder="e.g. Technical Issue" name="name" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal modal-blur fade" id="kt_modal_edit_category" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="kt_modal_edit_category_form" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Support Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Category Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
    <script>
        $(function () {
            var table = $('#kt_categories_table').DataTable({
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
                order: [], // Disable initial sorting to keep S.No sequential
                columnDefs: [
                    { orderable: false, targets: [0, 5] }
                ],
                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
            });

            $('#dt-search').on('input', function () {
                table.search($(this).val()).draw();
            });
        });
    </script>
@endpush

@push('scripts')
<script>
    function editCategory(id, name, status) {
        var form = document.getElementById('kt_modal_edit_category_form');
        form.action = "{{ route('admin.support.categories.index') }}/" + id;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_status').value = status;
        
        var editModal = new bootstrap.Modal(document.getElementById('kt_modal_edit_category'));
        editModal.show();
    }
</script>
@endpush
