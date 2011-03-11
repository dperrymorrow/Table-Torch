# Table Torch Scaffolding Spark

## !version 1.0.3 onward uses key::value params to avoid requiring params at certain positions of the URI. Why? So you use Table Torch in sub directories. */admin/pages/torch_it_up* will now work as expected. 

Table Torch is a custom scaffolding system that can be used for common administration tasks of tables in your CodeIgniter Application. Table Torch is different from the traditional scaffold you are used to in the fact that its customizable, and is quite extensible.

## Core Features

-  Set what tables and what fields of those tables you would like editable by Table Torch in your config file
-  Set what actions can be performed on each table, add, edit, delete
-  Searchable, sortable Listing pages
-  After editing or adding, you will return to the listing page in the previous state, pagination, sorting, search criteria in tact.
-  Views can be overridden from your controller
-  CRUD callbacks available to your controller
-  Version 1.0.3 on supports nested directories, your URI can now be however you need, Table Torch uses its own key::value params 

![Table Torch](http://dl.dropbox.com/u/9683877/spark_imgs/table_torch.png "Table Torch Example")



## Setup Your Config Preferences

In sparks/table-torch/config/table_torch.php you will find the preferences for running your scaffold. The config file is well commented.

## Basic Usage From Your Controller

	function torch(){
		// you can do this in any method you like
		// !! you would obviously need to do your authorization prior to letting the world see your Table Torch
		$this->load->spark( 'table-torch/[version #]');
		$this->table_torch->route();
	}

## Overriding the Table Torch Views
You have the ability to override any action of Table Torch. To do so just add the action to the controller from which you used Table Torch and it will use your method instead, while still passing you all the data that Table Torch fetched for the page. The example below overrides edit page of the "users" table.

### Actions That Can Be Overridden

-  [tableName]_add ( creating a new row )
-  [tableName]_edit ( the editing of an existing row )
-  [tableName]_listing ( the listing of all rows )

### Loading Your Own View after an override

If you wish to load your own view for the page, specify FALSE in the third param. Or you can use the Table torch view. Either way, you will be using the template file specified in the *sparks/table-torch/[ version # ]/config/table_torch.php* file

	// your custom view loaded
	function users_edit( $data ){
		// print_r( $data );
		$this->table_torch->load_view( 'user/edit', $data, FALSE );
	}
	
	// the normal Table Torch view loaded
	function users_edit( $data ){
		// print_r( $data );
		$this->table_torch->load_view( 'form', $data, TRUE );
	}

## Callback Hooks
You can preform additional functions before or after the Table Torch form submissions. Available callbacks are 

-  before_insert
-  after_insert
-  before_update
-  after_update
-  before_delete
-  after_delete

## Example of Callbacks

	function before_delete( $table, $primary_key ){
		/*
		do what you need to before deleting the row here, 
		you are given the table and primary key being deleted ( normally id, but whatever you set as primary key )
		*/
	}

	function before_insert( $table, $data ){
		/*
		do what you need to do before inserting
		You must return the data that will be inserted
		*/
		return $data;
	}

	function before_update( $table, $data ){
		/*
		do what you need to before updating a row
		you must return the data from this method!
		*/
		return $data;
	}

	function after_insert( $table, $data ){
		/*
		do what you need to do after inserting
		the primary key is returned in your data using insert_id();
		*/
	}

	function after_update( $table, $data ){
		/* do what you need to after updating a row */
	}

- [Log Issues or Suggestions](https://github.com/dperrymorrow/Table-Torch/issues)
- [Follow me on Twitter](http://twitter.com/dperrymorrow)

