<td>
  <a href="{{ url('/notes/' . $note->id) }}" class="btn btn-block btn-primary">View</a>
</td>
<td>
  <a href="{{ url('/notes/' . $note->id . '/edit') }}" class="btn btn-block btn-primary">Edit</a>
</td>
<td>
  <form action="{{ route('notes.destroy', $note->id ) }}" method="POST">
      @method('DELETE')
      @csrf
      <button class="btn btn-block btn-danger">Delete</button>
  </form>
</td>
