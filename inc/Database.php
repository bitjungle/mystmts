<?php 
/**
 * Database class for mystmts using PDO
 * 
 * @author  Rune Mathisen <devel@bitjungle.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3
 */
class Database extends PDO
{

    /**
     * Create a new DB object
     * 
     * @param string $settings Database connect info
     */
    public function __construct($settings)
    {
        $dsn = $settings['driver'] . 
        ':dbname=' . $settings['dbname'] .
        ';host=' . $settings['host'];
        
        parent::__construct(
            $dsn, 
            $settings['user'], 
            $settings['passwd']
        );

    }

    /**
     * Henter en oversikt over alle saker i databasen
     * 
     * @return array|false
     */
    public function getStatementList()
    {
        $query = 'SELECT id, case_name, preamble, case_date 
                  FROM mystmts 
                  WHERE deleted=0
                  ORDER BY case_date DESC, case_name';
        $stmt = $this->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Henter et spesifikt saksinnlegg fra databasen
     * 
     * @param string $id Saksid
     * 
     * @return array|false
     */
    public function getStatement($id)
    {
        //TODO replace * with specific columns
        $query = 'SELECT * FROM mystmts WHERE id=:id_string LIMIT 1';
        $stmt = $this->prepare($query);
        $stmt->execute(['id_string' => "{$id}"]);
        $s = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (isset($s[0])) {
            return $s[0];
        } else {
            return false;
        }
    }

    /**
     * Søker i databasen for en spesifikk streng
     * 
     * @param string $str Søkestrengen
     * 
     * @return array|false
     */
    public function search($str) 
    {
        $query = 'SELECT id, case_name, preamble 
                  FROM mystmts
                  WHERE (case_name LIKE :search_string 
                  OR preamble LIKE :search_string 
                  OR statement_txt LIKE :search_string) 
                  ORDER BY case_date DESC';
        $stmt = $this->prepare($query);
        $stmt->execute(['search_string' => "%{$str}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    /**
     * Legg til nytt innlegg
     * 
     * @param string $arr 
     * 
     * @return bool
     */
    public function add($arr) 
    {
        $query = 'INSERT INTO `mystmts` 
                         (`case_name`, 
                          `preamble`, 
                          `statement_txt`, 
                          `img_file_name`, 
                          `img_attrib`, 
                          `case_date`, 
                          `case_doc_url`, 
                          `added_date`, `changed_date`, `deleted`)
                  VALUES (:value_name, 
                          :value_pre, 
                          :value_stmt, 
                          :value_img, 
                          :value_img_attrib, 
                          :value_date, 
                          :value_url, 
                          now(), now(), 0);';
        $stmt = $this->prepare($query);
        $ok = $stmt->execute(
            ['value_name' => $arr['case_name'],
            'value_pre' => $arr['preamble'],
            'value_stmt' => $arr['statement_txt'],
            'value_img' => $arr['img_file_name'],
            'value_img_attrib' => $arr['case_img_attrib'],
            'value_date' => $arr['case_date'],
            'value_url' => $arr['case_doc_url']]
        );
        if ($ok) {
            return intval($this->lastInsertId());
        } else {
            return false;
        }
    }

    /**
     * Oppdater innlegg
     * 
     * @param string $arr 
     * 
     * @return bool
     */
    public function update($arr) 
    {
        $query = 'UPDATE `mystmts` SET
                    `case_name` = :value_name,
                    `preamble` = :value_pre,
                    `statement_txt` = :value_stmt,
                    `img_file_name` = :value_img,
                    `img_attrib` = :value_img_attrib, 
                    `case_date` = :value_date,
                    `case_doc_url` = :value_url,
                    `changed_date` = now()
                  WHERE `id` = :value_id;';
        $stmt = $this->prepare($query);
        $ok = $stmt->execute(
            ['value_name' => $arr['case_name'],
            'value_pre' => $arr['preamble'],
            'value_stmt' => $arr['statement_txt'],
            'value_img' => $arr['img_file_name'],
            'value_img_attrib' => $arr['case_img_attrib'],
            'value_date' => $arr['case_date'],
            'value_url' => $arr['case_doc_url'],
            'value_id' => $arr['id']]
        );
        if ($ok) {
            return intval($arr['id']);
        } else {
            return false;
        }
    }
}
?>