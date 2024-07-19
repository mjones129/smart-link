<?php

class SLinit {

  public function sl_create_tokens_table() {
    global $wpdb;
    //get table name
    $sl_tokens = $wpdb->prefix . 'sl_tokens';
    $charset_collate = $wpdb->get_charset_collate();
    //check if tokens table exists
    if($wpdb->get_var("SHOW TABLES LIKE '$sl_tokens'") != $sl_tokens) {
      // SQL to create the tokens table
      $sql = "CREATE TABLE $sl_tokens (
        id INT NOT NULL AUTO_INCREMENT,
        slug VARCHAR(255) NOT NULL,
        token VARCHAR(32) NOT NULL,
        expiration DATETIME NOT NULL,
        used TINYINT DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE (token)
      ) $charset_collate;";

      dbDelta($sql);  
  
    }
  
  } 

  public function sl_create_smtp_table() {
    global $wpdb;
    //get table name
    $sl_smtp_creds = $wpdb->prefix . 'sl_smtp_creds';
    $charset_collate = $wpdb->get_charset_collate();
    //check if smtp table exists
    if($wpdb->get_var("SHOW TABLES LIKE '$sl_smtp_creds'") != $sl_smtp_creds) {
      // SQL to create the SMTP credentials table
        $sql2 = "CREATE TABLE $sl_smtp_creds (
          id TINYINT NOT NULL DEFAULT 1,
          host VARCHAR(100) NOT NULL,
          port SMALLINT NOT NULL,
          username VARCHAR(100) NOT NULL,
          name VARCHAR(100) NOT NULL,
          password VARCHAR(255) NOT NULL,
          first_time TINYINT NOT NULL,
          PRIMARY KEY (id)
      ) $charset_collate;";

      // SQL to insert default values into the SMTP credentials table
      $sql3 = "INSERT INTO $sl_smtp_creds (host, port, username, name, password, first_time)
               VALUES ('hostname-here', 9000, 'fake-user', 'John Doe', 'fake-pass', 1);";

    //create the smtp creds table and insert placeholder data
     dbDelta($sql2);
     $wpdb->query($sql3);

    }
  }


}




