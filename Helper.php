<?php

class Helper {

    public static function removeLineBreaks(array $dados = null): array
    {

        if (empty($dados)) return null;

        $new_data = array();
        foreach ($dados as $key => $value) {
            $new_data[] = preg_replace( "/\r|\n/", "",$value);
        }

        return $new_data;

    }

}