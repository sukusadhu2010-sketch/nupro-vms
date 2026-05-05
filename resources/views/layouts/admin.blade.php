@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(message) {
            return Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    return true;
                }
                return false;
            });
        }

        function confirmStatusToggle(message) {
            return Swal.fire({
                title: 'Confirm Status Change?',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    return true;
                }
                return false;
            });
        }

        // AJAX Delete
        document.querySelectorAll('form[action*="/destroy"]').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!await confirmDelete(this.dataset.message || 'Are you sure?')) return;

                try {
                    const response = await fetch(this.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    });

                    if (response.ok) {
                        this.closest('tr').remove();
                        Swal.fire('Deleted!', 'Vendor has been deleted.', 'success');
                    } else {
                        throw new Error('Delete failed');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        });

        // AJAX Status Toggle
        document.querySelectorAll('form[action*="/toggle-status"]').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!await confirmStatusToggle(this.dataset.message || 'Confirm?')) return;

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Update UI
                        const statusCell = this.closest('tr').querySelector('td:nth-child(6)');
                        statusCell.innerHTML = data.status_badge;
                        Swal.fire('Updated!', 'Status changed successfully.', 'success');
                    } else {
                        throw new Error('Update failed');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        });
    </script>
@endpush
