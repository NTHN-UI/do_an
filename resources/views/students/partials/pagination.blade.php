@if($students->lastPage() > 1)
    <div class="card-footer border-0 bg-transparent" id="pagination-container">
        <nav aria-label="page navigation">
            {{ $students->links('pagination::bootstrap-5') }}
        </nav>
    </div>
@endif
