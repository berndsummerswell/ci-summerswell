<?php
  /**
   * -----------------------------------------------------------------------------
   * ui_helper.php
   * 
   * summerswell UI helpers
   * These are functions to help format the UI elements or to convert
   * between UI and PHP elements.
   * -----------------------------------------------------------------------------
   */

  /**
   * Converts between MySQL boolean values and HTML checkbox values
   */
  function bools_to_checkbox( &$data, $items ) {
    foreach( $items as $item ) {
      $data[$item] = $data[$item] == 1 ? "checked" : ""; 
    }
  }
  
  /**
   * Converts between HTML checkbox values and MySQL boolean values
   */
  function checkbox_to_bools( &$data, $items ) {
    foreach ( $items as $item ) {
      if ( isset( $data[$item] ) ) {
        $data[$item] = ( $data[$item] == "on" ? 1 : 0 );
      } else {
        $data[$item] = 0;
      }
    }
  }
  
  /*
   * Formats an input as a currency value
   */
  function to_currency( $input ) {
    return number_format( $input, 2, ".", " " ); 
  }
  
  /*
   * Formats Array items as currencies
   */
  function format_currencies( &$data, $items ) {
    foreach ( $items as $item ) {
      if ( isset( $data[$item] ) ) {
        $data[$item] = to_currency( $data[$item] );
      }
    }
  }
  
  /*
   * Formats Array items as MSSQL Guid strings
   */
  function format_dbguid( &$data_array, $fields ) {
    if (is_string($fields)) {
      $fields = explode( ";", $fields );
    }
    foreach( $fields as $field ) {
      $data_array[$field] = mssql_guid_string($data_array[$field]);
    }
    
  }
  
  /*
   * Formats Array items as Time values
   */
  function format_astime( &$data_array, $fields ) {
    foreach( $fields as $field ) {
      $time =  new DateTime( $data_array[$field] );
      $data_array[$field] = $time->format( "H:i:s" );
    }
  }

  /*
   * Formats Array items as Date values
   */
  function format_asdate( &$data_array, $fields ) {
    foreach( $fields as $field ) {
      $date =  new DateTime( $data_array[$field] );
      $data_array[$field] = $date->format( "Y-m-d" );
    }
  }

  /*
   * Returns an Array of values formatted as time
   */
  function array_fmt_astime( $data_array, $fields ) {
    format_astime( $data_array, $fields);
    return $data_array;
  }
 
  /*
   * Returns an Array of values formated as date
   */
  function array_fmt_asdate( $data_array, $fields ) {
    format_asdate( $data_array, $fields);
    return $data_array;
  }
  
  /*
   * Returns an Array of Array of values formatted as time
   */
  function all_array_fmt_astime( $result_array, $fields ){
    foreach( $result_array as &$data_array ) {
      format_astime( $data_array, $fields);
    }
    return $result_array;
  } 
  
  /*
   * Returns an Array of Array of values formatted as date
   */
  function all_array_fmt_asdate( $result_array, $fields ){
    foreach( $result_array as &$data_array ) {
      format_asdate( $data_array, $fields);
    }
    return $result_array;
  } 
  
  /* 
   * Returns an Array of Array of values formatted as MSSQL GUID strings
   */
  function all_array_fmt_dbguid( $result_array, $fields ) {
    foreach ( $result_array as &$data_array ) {
      format_dbguid($data_array, $fields);
    }
    return $result_array;
  }
  
  /*
   * Returns an Array of values formatted as currency
   */
  function array_format_currency( $data, $columns ) {
    format_currencies( $data, $columns );
    return $data;
  }
  
  /*
   * Returns an Array of values converted from a currency amount to a float
   */
  function array_format_currencyrev( $data, $columns ) {
    foreach( $columns as $col ) {
      $data[$col] = number_format_rev($data[$col]);
    }
    return $data;
  }
  
  /*
   * Returns an Array of Array of values converted from a currency amount to a float
   */
  function all_array_format_currencyrev( $data, $columns ) {
    foreach( $data as &$item ) {
      $item = array_format_currencyrev($item, $columns);
    }
    return $data;
  }
  
  /*
   * Converts a currency string to a float
   */
  function number_format_rev( $float ) {
    return (float) str_replace(' ', '', $float );
  }
  
  /*
   * Formats an array of values as currency
   */
  function array_format_currencies( &$datalist, $items ) {
    foreach( $datalist as &$data ) {
      format_currencies($data, $items);
    }
  }
  
  /*
   * Prefixes a string to keys in an array
   */
  function array_prefix( $prefix, $array ) {
    return array_combine(array_map(create_function('$k', 'return "'.$prefix.'".$k;'), array_keys($array)), array_values($array));
  }
  
  /*
   * Returns the SUM of values in an Array
   */
  function array_add_values( $datalist, $field ) {
    $result = 0;
    foreach( $datalist as $data ) {
      $result += doubleval( number_format_rev( $data[$field] ) );
      
    }
    return $result;
  }

?>
