<?php

namespace App\DataTables;

use App\Models\Notes;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class NotesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($note) {
                return view('dashboard.notes.action', compact('note'));
            })
            ->rawColumns(['action']);

    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Notes $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Notes $model)
    {
        return $model->newQuery()
        ->select('notes.id', 'title', 'content','note_type','applies_to_date' ,'users.name as author', 'status.name as status_name')
           ->leftJoin('users', 'users.id', '=', 'notes.users_id')
           ->leftJoin('status', 'status.id', '=', 'notes.status_id')
           ->with(['user', 'status']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('notes-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1);

    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
          'id' => ['title' => 'Id'],
          'author' => ['title' => 'Author'],
            'title' => ['title' => 'Title'],
            'content' => ['title' => 'Content'],
            'applies_to_date' => ['title' => 'Applies to date'],
            'status_name' => ['title' => 'Status'],
            'note_type'=> ['title' => 'Note type'],
            Column::computed('action')
               ->exportable(false)
               ->printable(false)
               ->width(120)
               ->addClass('text-center')

        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Notes_' . date('YmdHis');
    }
}
