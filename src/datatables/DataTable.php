<?php

namespace Ssentezo\DataTables;

use Ssentezo\Util\AppUtil;
use Ssentezo\Database\DbAccess;

class DataTable
{

    protected $draw;
    protected $columns;
    protected $order;
    protected $start;
    protected $length;
    protected $search;
    protected $query;
    protected $table;
    protected $where_condition;
    protected $search_clause;
    protected $total_records;
    protected $orderby;
    protected $total_records_query;
    protected  $total_filtered_query;
    protected $data_query;

    public function __construct($db, $request, $columns, $table)
    {
        $this->draw = $request['draw'];
        $this->columns = $columns;
        $this->order = $request['order'];
        $this->start = $request['start'];
        $this->length = $request['length'];
        $this->search = $request['search'];
        $this->table = $table;
    }
    /**
     * Add a search section to the where clause of the main query
     * @param callback $callback  a function that takes in the search string and returns the search part of the query i.e function($search_text){ return "(fname like '%$search_text%' OR lname like '%$search_text%')"}
     * @return string The final version of the search.
     */
    public function search($callback = '')
    {
        if (!empty($this->search['value'])) {

            $search_value = $this->search['value'];
            if (is_callable($callback)) {
                $this->search_clause  = call_user_func($callback, $search_value);
            }
        }
        return $this->search_clause;
    }
    /**
     * Creates the orderby clause for the  data query
     * @return string Returns the order by clause of the data query
     */
    protected function orderBy()
    {
        if ($this->length == '-1') {

            $this->order_by = " ORDER BY " . $this->columns[$this->order[0]['column']] . "   " . $this->order[0]['dir'];
        } else {
            $this->order_by .=  " ORDER BY " . $this->columns[$this->order[0]['column']] . "   " . $this->order[0]['dir'] . "  LIMIT " . $this->start . " ," . $this->length . " ";
        }
        return $this->orderby;
    }

    protected function buildQuery()
    {
        $this->total_records_query = $this->db->select($this->table, ['count(id) as num ']);
        $this->query = " SELECT * FROM borrower ";
        $this->search();
        $this->orderBy();
    }
    public function getData($db)
    {
        $this->buildQuery();
        return $db->selectQuery($this->query);
    }
}
