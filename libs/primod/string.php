<?php

/**
 * This class provides set of methods for peforming advance
 * string manipulation operation required for mail merge etc.
 *
 * @author Tushar Takkar<ttakkar@primarymodules.com>
 */
class string
{

    /**
     * This method helps in splitting given string into multi-dimentional array based on each rule.
     * Rules are regex for
     * 1)capturing merge words.
     * 2)optout links.
     * 3)links
     * 4).. practically any regex.
     *
     * @author Tushar Takkar<ttakkar@primarymodules.com>
     * @param string $string this contain string to be splitted
     * @param array $rules this contain array of rules
     * @return mixed This array contain 2 elements. first element contain multi-dimentional array, one
     * dimention pertaining to each rule.
     * 2nd element contain 2 dimentional array, first key is rule name and 2nd key is match words form given string.
     * $string=" hi im tushar takkar. call me on {{Contact.phone}} "
     * $rules=array("merge_words"=>"/\{\{.*\}\}/i");
     */
    public static function split($string, $rules)
    {
        $parts = array();
        $on = array();
        $tempOn = array();
        $r = each($rules);
        array_shift($rules);
        $parts = preg_split($r["value"], $string, null);
        preg_match_all($r["value"], $string, $tempOn);
        $on[$r["key"]] = $tempOn[0];
        if (!empty($rules)) {
            foreach ($parts as $partNo => $part) {
                $array = self::split($part, $rules);
                list($p, $o) = $array;
                $parts[$partNo] = $p;
                $on = array_merge_recursive($on, $o);
            }
        }
        return array($parts, $on);
    }

    /**
     * Handles merging of parts of mails
     *
     * @author Tushar Takkar<ttakkar@primarymodules.com>
     * @param array $parts
     * @param array $tobeMerge
     * @return string
     */
    public static function merge($parts, $tobeMerge)
    {
        $partsCopy = $parts;
        $tobeMergeCopy = array_values($tobeMerge);
        self::_merge($partsCopy, $tobeMergeCopy, 0);
        return $partsCopy;
    }

    /**
     * Helper function for merge()
     *
     * @author Tushar Takkar<ttakkar@primarymodules.com>
     * @param mixed $parts
     * @param mixed $tobeMerge
     * @param int $level
     * @return string
     */
    private static function _merge(&$parts, &$tobeMerge, $level)
    {
        if (isset($parts[0]) && is_array($parts[0]) && isset($parts[0][0])) {
            foreach ($parts as $partNo => $part) {
                $nl = $level + 1;
                self::_merge($parts[$partNo], $tobeMerge, $nl);
            }
        }

        $count = count($parts);
        $merge = "";
        for ($i = 0; $i < $count; $i++) {
            if (isset($parts[$i + 1])) {
                $val = (
                        isset($tobeMerge[$level])
                        && is_array($tobeMerge[$level]) ? array_shift($tobeMerge[$level]) : ""
                        );
                $merge .= ( isset($parts[$i]) ? $parts[$i] : "") . $val;
            } else {
                $merge .= ( isset($parts[$i]) ? $parts[$i] : "");
            }
        }

        $parts = $merge;
        return;
    }

    /**
     * Filter unwanted charectors from merge words
     *
     * @author Tushar Takkar<ttakkar@primarymodules.com>
     * @param array $mergeWords
     * @return array
     */
    public static function cleanWords($mergeWords,$wrapper=array("{", "}"))
    {
        if (is_array($mergeWords))
                foreach ($mergeWords as $k => $word) {
                $mergeWords[$k] = str_replace($wrapper, "", $word);
            }
        return $mergeWords;
    }

    /**
     * @todo : to be done
     * @param type $mergeUrls
     * @return type
     */
    public static function cleanUrls($mergeUrls)
    {
        if (is_array($mergeUrls))
                foreach ($mergeUrls as $k => $url) {
                $tempOn = array();
                preg_match_all("/'(?:[^\\']+|\\.)*'|\"(?:[^\\\"]+|\\.)*\"/", $url, $tempOn);
                if (isset($tempOn[0]) && isset($tempOn[0][0])) $mergeUrls[$k] = substr($tempOn[0][0], 1, -1);
                else throw new Exception(sprintf(_('Not a valid url "%s"'), $url));
            }
        return $mergeUrls;
    }

    /**
     * Merge words with data
     *
     * @author Tushar Takkar<ttakkar@primarymodules.com>
     * @param array $mergeWords
     * @param array $data
     * @return string
     */
    public static function populateWords($mergeWords, $data)
    {
        $mergedData = array();
        foreach ($mergeWords as $word) {
            $val = self::extract($data, $word);
            if (is_array($val)) {
                $val = "";
            }
            $mergedData[] = $val;
        }
        return $mergedData;
    }

    /**
     * Search given path in array and return found value
     *
     * @author Tushar Takkar<ttakkar@primarymodules.com>
     * @param string $path
     * @param array $data
     * @param string $value
     * @return string
     */
    public static function extract($data, $path, $value='')
    {
        if (empty($path)) return $value;
        $path = (is_string($path) ? explode(".", $path) : $path);
        $temp = &$data;
        $found = false;
        while (!empty($path)) {
            $key = array_shift($path);
            if (isset($temp[$key])) {
                $found = true;
                $temp = &$temp[$key];
            } else {
                break;
            }
        }
        return ($found == true ? $temp : $value);
    }

}