<?php

class ilExerciseSettingsMapper extends ilDataMapper {
    /**
     * @var string
    */
    protected $tableName = 'rep_robj_xtov_e2o';
    protected function getSelectPart() {
        $fields = array(
			'ref.obj_id',
			'ref.ref_id',
			'od.title',
			'od.description'
        );

        return implode(', ', $fields);
    }

    protected function getFromPart() {
        $joins = array(
                    'INNER JOIN object_reference ref ON (rep_robj_xtov_e2o.obj_id_exercise = ref.obj_id AND ref.deleted IS NULL)',
                    "INNER JOIN object_data od ON (ref.obj_id = od.obj_id) AND od.type = 'exc'"
        );

		return $this->tableName . ' ' . implode(' ', $joins);
    }

    protected function getWherePart(array $filters) {
        $conditions = array('rep_robj_xtov_e2o.obj_id_overview = ' . $this->db->quote($filters['overview_id'], 'integer'));
        
        if(isset($filters['flt_tst_name']) && !empty($filters['flt_tst_name']))
            {
                    $conditions[] = $this->db->like('od.title', 'text', '%' . $filters['flt_tst_name'] . '%', false);
            }
        
        return implode(' AND ', $conditions);
    }

}


