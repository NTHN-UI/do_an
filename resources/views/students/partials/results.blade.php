@foreach($students as $index => $student)
    <tr class="text-center">
        <td class="ps-4">{{ $index + 1 }}</td>
        <td>{{ $student->full_name }}</td>
        <td>{{ $student->email }}</td>
        <td class="text-center">{{ $student->phone }}</td>
        <td>{{ $student->school->name ?? 'N/A' }}</td>
        <td class="text-center">
            @if($student->is_active)
                <span class="badge bg-primary-color">Hoạt động</span>
            @else
                <span class="badge bg-secondary">Ngừng</span>
            @endif
        </td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <i class="fas fa-ellipsis-v text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0"
                    data-bs-popper="static">
                    <li>
                        <a class="dropdown-item px-3 py-2"
                           href="{{ route('students.show', $student->id) }}">
                            Xem
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item px-3 py-2"
                           href="{{ route('students.edit', $student->id) }}">
                            Sửa
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach
