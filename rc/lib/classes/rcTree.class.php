<?php
/**
 * Class rcTree
 * @property $basic_tree
 */
class rcTree
{
    protected $tree = array();
    protected $basic_tree = array();
    protected $counter = 0;

    /**
     * rcTree constructor.
     * @param null|array $tree an array containing the keys id and parent_id in each element
     */
    public function __construct($tree = null)
    {
        if (isset($tree) && is_array($tree)) {
            $this->setTree($tree);
            $this->treeCalculate();
        }
    }

    /**
     * @param $type string parameter name
     * @return mixed
     */
    public function __get($type)
    {
        $allowed_values = array('basic_tree');
        $result = false;
        if (isset($this->$type) && in_array($type, $allowed_values)) {
            $result = $this->$type;
        }
        return $result;
    }

    /**
     * @param $tree array an array containing the keys id and parent_id in each element
     * the method builds a recursive tree, the keys contain the id of the elements of the input array
     */
    public function setTree(array $tree)
    {
        $this->tree = array();
        foreach ($tree as $element) {
            $this->basic_tree[$element['id']] = $element;
            $this->tree[$element['parent_id']][$element['id']] = array();
        }
        foreach ($this->tree as $key => $row) {
            foreach ($row as $child => $value) {
                $this->treeBuilding($key, $child);
            }
        }
        if (isset($this->tree[0])) {
            $this->tree = $this->tree[0];
        }
    }

    /**
     * @param $parent int an a parent id
     * @param $key int an a child id
     * helper recursive tree building method
     */
    protected function treeBuilding($parent, $key)
    {
        if (isset($this->tree[$key]) && count($this->tree[$key]) > 0) {
            foreach ($this->tree[$key] as $child => $col) {
                if (count($col) == 0) {
                    $this->treeBuilding($key, $child);
                }
            }
        }
        if(isset($this->tree[$parent])){
            if (isset($this->tree[$key])) {
                $this->tree[$parent][$key] = $this->tree[$key];
            } else {
                $this->tree[$parent][$key] = null;
            }
        }
        unset($this->tree[$key]);
    }

    /**
     * @param string|array $tree
     * @param int $depth an a element depth
     * @param int $brunch_key an a parent_id
     * the method adds to the original array of matches id - parent_id the values of depth, right and left array keys
     */
    protected function treeCalculate($tree = 'start', $depth = -1, $brunch_key = 0)
    {
        if ($tree == 'start') {
            $tree = $this->tree;
            $this->counter = 0;
        }
        if (isset($this->basic_tree[$brunch_key])) {
            $this->basic_tree[$brunch_key]['depth'] = $depth;
            $this->counter++;
            $this->basic_tree[$brunch_key]['left_key'] = $this->counter;
        }
        if (isset($tree) && count($tree) > 0) {
            foreach ($tree as $key => $brunch) {
                $this->treeCalculate($brunch, $depth + 1, $key);
            }
        }
        if (isset($this->basic_tree[$brunch_key])) {
            $this->counter++;
            $this->basic_tree[$brunch_key]['right_key'] = $this->counter;
        }
    }
}