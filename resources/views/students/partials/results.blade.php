@foreach($students as $index => $student)
    <tr>
        <td class="ps-4">{{ $index + 1 }}</td>
        <td>{{ $student->full_name }}</td>
        <td>{{ $student->email }}</td>
        <td class="text-center">{{ $student->phone }}</td>
        <td>{{ $student->school->name ?? 'N/A' }}</td>
        <td class="text-center">
            <span
                class="badge rounded-pill {{ $student->is_active ? 'bg-success' : 'bg-secondary' }}">
                {{ $student->is_active ? 'Hoạt động' : 'Ngừng' }}
            </span>
        </td>
        <td class="text-end pe-4">
            <div class="dropdown">
                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <i class="fas fa-ellipsis-v text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 z-10"
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
                    <li>
                        <form action="{{ route('students.destroy', $student->id) }}"
                              method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item px-3 py-2 shadow"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa học sinh này?')">
                                Xóa
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach
