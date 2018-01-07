<?php

namespace Models;

class BaseModel{

    protected $table;

    public function __construct( ) {
        $this->table = '';
    }

    public function fetch( $what = null, $table = null, array $options = array() ){
        $orderBy = "";
        if( is_array($what) && count($what) ){
            if( isset($options) && isset($options['raw']) && $options['raw'] === true ){
                $select = implode(",",$what);
            } else{
                $select = "`".implode("`,`",$what)."`";
            }
        } elseif( strtolower($what) == 'all' ){
            if(isset($table) && $table!=null){
                $this->table = $table;
            }

            if(!isset($table)){
                $trace = debug_backtrace(); //_pr($trace);
                $this->table = strtolower( $trace[0]['class']);

                if( isset($trace[1])){
                    $this->table = strtolower( $trace[1]['class']);
                }
            }
            $select = "*";
        }

        if($select==''){
            return false;
        }

        if(isset($options) && isset($options['orderBy']) && isset($options['order'])){
            $orderBy = " ORDER BY ".db::q($options['orderBy'])." ".$options['order'];
        }

        try{
            $query = db::con()->query('select '.$select.' from '.db::q(db::p().$this->table).' WHERE deleted=0 '.$orderBy);
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
         } catch(Exception $e){
            _pr($e,1);
        }
    }

    public function fetchSingle( $id, $what = null, $table = null, array $options = array()){
        if($id<=0){
            return false;
        }
        if( is_array($what) && count($what) ){
            if( isset($options) && isset($options['raw']) && $options['raw'] === true ){
                $select = implode(",",$what);
            } else{
                $select = "`".implode(",`",$what)."`";
            }
        } elseif( strtolower($what) == 'all' ){
            if(isset($table) && $table!=null){
                $this->table = $table;
            }

            if(!isset($table)){
                $trace = debug_backtrace(); //_pr($trace);
                $this->table = strtolower( $trace[0]['class']);

                if( isset($trace[1])){
                    $this->table = strtolower( $trace[1]['class']);
                }
            }
            $select = "*";
        }

        if($select==''){
            return false;
        }

        try{
            $stmt = db::con()->prepare('select '.$select.' from '.db::q(db::p().$this->table).' WHERE id=:id AND deleted=0 ');
            $stmt->execute(array(':id' => $id));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch(Exception $e){
            _pr($e,1);
        }
    }

}