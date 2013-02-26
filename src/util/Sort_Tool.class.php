<?php

Import::php("util.ArrayList");

/**
 * Sorting Tool
 * @package OpenM 
 * @subpackage util 
 * @copyright (c) 2013, www.open-miage.org
 * @license http://www.apache.org/licenses/LICENSE-2.0 Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @link http://www.open-miage.org
 * @author Gael SAUNIER
 */
abstract class Sort_Tool {

    const ASC = true;
    const DESC = false;

    /**
     * used to sort an ArrayList
     * @param ArrayList $arrayList is arrayList to sort
     * @param $option is equal to true if ascendant sort is required, else false
     */
    public static function sort(ArrayList $arrayList, $option = self::ASC) {
        if (!is_bool($option))
            throw new InvalidArgumentException("the second argument must be a boolean");
        if ($arrayList->size() < 2)
            return;

        $minVal = 1;
        $maxVal = $arrayList->size();
        $compareTo = (int) $option ? 1 : -1;

        $vector = $arrayList->toArray();
        
        $modification = false;
        for ($i = $minVal; $i < $maxVal; $i++) {
            $curObject = $vector[$i - 1];
            $nextObject = $vector[$i];
            if ($curObject->compareTo($nextObject) * $compareTo > 0) {
                $vector[$i - 1] = $nextObject;
                $vector[$i] = $curObject;
                $modification = true;
            }
        }
        if (!$modification) {
            $arrayList->clear();
            $arrayList->addAll(ArrayList::from($vector));
        }

        for ($i = $maxVal; $i > $minVal; $i--) {
            $curObject = $vector[$i - 2];
            $nextObject = $vector[$i - 1];
            if ($curObject->compareTo($nextObject) * $compareTo > 0) {
                $vector[$i - 2] = $nextObject;
                $vector[$i - 1] = $curObject;
                $modification = true;
            }
        }
        
        while ($modification) {
            if ($minVal + 3 > $maxVal) {
                $arrayList->clear();
                $arrayList->addAll(ArrayList::from($vector));
            }
            $minVal +=1;
            $maxVal -=1;
            $modification = false;
            for ($i = $minVal; $i < $maxVal; $i++) {
                $curObject = $vector[$i - 1];
                $nextObject = $vector[$i];
                if ($curObject->compareTo($nextObject) * $compareTo > 0) {
                    $vector[$i - 1] = $nextObject;
                    $vector[$i] = $curObject;
                    $modification = true;
                }
            }
            if (!$modification) {
                $arrayList->clear();
                $arrayList->addAll(ArrayList::from($vector));
            }

            for ($i = $maxVal; $i > $minVal; $i--) {
                $curObject = $vector[$i - 2];
                $nextObject = $vector[$i - 1];
                if ($curObject->compareTo($nextObject) * $compareTo > 0) {
                    $vector[$i - 2] = $nextObject;
                    $vector[$i - 1] = $curObject;
                    $modification = true;
                }
            }
        }
    }

}

?>