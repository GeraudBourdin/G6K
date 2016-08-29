<?php

/*
The MIT License (MIT)

Copyright (c) 2015 Jacques Archimède

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace EUREKA\G6KBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use EUREKA\G6KBundle\Entity\Database;
use EUREKA\G6KBundle\Entity\JSONToSQLConverter;
use EUREKA\G6KBundle\Entity\DOMClient as Client;
use EUREKA\G6KBundle\Entity\ResultFilter;

use Silex\Application;
use Binfo\Silex\MobileDetectServiceProvider;

class DataSourcesAdminController extends BaseAdminController {
	
	private $log = array();
	private $datasources = array();
	
	private $db_dir;
	
	private $request;
	private $script;
	
	private $datatypes = array(
		'sqlite' => array(
			'array' => 'TEXT',
			'boolean' => 'BOOLEAN',
			'choice' => 'INTEGER',
			'country' => 'INTEGER',
			'date' => 'DATE',
			'day' => 'INTEGER',
			'department' => 'TEXT',
			'integer' => 'INTEGER',
			'money' => 'REAL',
			'month' => 'INTEGER',
			'multichoice' => 'TEXT',
			'number' => 'REAL',
			'percent' => 'REAL',
			'region' => 'INTEGER',
			'text' => 'TEXT',
			'textarea' => 'TEXT',
			'year' => 'INTEGER'
		),
		'pgsql' => array(
			'array' => 'TEXT',
			'boolean' => 'SMALLINT',
			'choice' => 'SMALLINT',
			'country' => 'SMALLINT',
			'date' => 'DATE',
			'day' => 'SMALLINT',
			'department' => 'VARCHAR(3)',
			'integer' => 'INTEGER',
			'money' => 'REAL',
			'month' => 'SMALLINT',
			'multichoice' => 'TEXT',
			'number' => 'REAL',
			'percent' => 'REAL',
			'region' => 'SMALLINT',
			'text' => 'TEXT',
			'textarea' => 'TEXT',
			'year' => 'SMALLINT'
		),
		'mysql' => array(
			'array' => 'TEXT',
			'boolean' => 'TINYINT(1)',
			'choice' => 'INT',
			'country' => 'INT',
			'date' => 'DATE',
			'day' => 'INT',
			'department' => 'VARCHAR(3)',
			'integer' => 'INT',
			'money' => 'FLOAT',
			'month' => 'INT',
			'multichoice' => 'TEXT',
			'number' => 'FLOAT',
			'percent' => 'FLOAT',
			'region' => 'INT',
			'text' => 'TEXT',
			'textarea' => 'TEXT',
			'year' => 'INT'
		),
		'mysqli' => array(
			'array' => 'TEXT',
			'boolean' => 'TINYINT(1)',
			'choice' => 'INT',
			'country' => 'INT',
			'date' => 'DATE',
			'day' => 'INT',
			'department' => 'VARCHAR(3)',
			'integer' => 'INT',
			'money' => 'FLOAT',
			'month' => 'INT',
			'multichoice' => 'TEXT',
			'number' => 'FLOAT',
			'percent' => 'FLOAT',
			'region' => 'INT',
			'text' => 'TEXT',
			'textarea' => 'TEXT',
			'year' => 'INT'
		),
		'jsonsql' => array(
			'array' => 'array',
			'boolean' => 'boolean',
			'choice' => 'integer',
			'country' => 'integer',
			'date' => 'string',
			'day' => 'integer',
			'department' => 'string',
			'integer' => 'integer',
			'money' => 'number',
			'month' => 'integer',
			'multichoice' => 'object',
			'number' => 'number',
			'percent' => 'number',
			'region' => 'integer',
			'text' => 'string',
			'textarea' => 'string',
			'year' => 'integer'
		)
	);

	public function indexAction(Request $request, $dsid = null, $table = null, $crud = null) {
		$this->request = $request;
		$form = $request->request->all();
		$no_js = $request->query->get('no-js') || 0;
		$this->script = $no_js == 1 ? 0 : 1;

		$this->db_dir = $this->get('kernel')-> getBundle('EUREKAG6KBundle', true)->getPath()."/Resources/data/databases";
		if (file_exists($this->db_dir."/DataSources.xml")) {
			$this->datasources = new \SimpleXMLElement($this->db_dir."/DataSources.xml", LIBXML_NOWARNING, true);
		} else {
			$this->datasources = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><DataSources xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="../../doc/DataSources.xsd"><Databases></Databases></DataSources>', LIBXML_NOWARNING);
		}
		if ($crud !== null) {
			if (! $this->get('security.context')->isGranted('ROLE_CONTRIBUTOR')) {
				return $this->errorResponse($form, "Access denied!");
			}
			if ($crud == 'create-datasource') {
				return $this->createDatasource ($form);
			} elseif ($crud == 'import-datasource') {
				return $this->showDatasources(0, null, "import");
			} elseif ($crud == 'doimport-datasource') {
				return $this->doImportDatasource($request->files->all());
			} elseif ($crud == 'edit-datasource') {
				return $this->showDatasources($dsid, null, "edit");
			} elseif ($crud == 'doedit-datasource') {
				return $this->doEditDatasource ($dsid, $form);
			} elseif ($crud == 'drop-datasource') {
				return $this->dropDatasource ($dsid);
			} elseif ($crud == 'edit') {
				return $this->showDatasources($dsid, $table, 'edit-table');
			} else {
				$database = $this->getDatabase($dsid);
				switch ($crud) {
					case 'add':
						return $this->addTableRow ($form, $table, $database);
					case 'update':
						return $this->updateTableRow ($form, $table, $database);
					case 'delete':
						return $this->deleteTableRow ($form, $table, $database);
					case 'create':
						return $this->createTable ($form, $database);
					case 'doedit':
						return $this->doEditTable ($form, $table, $database);
					case 'drop':
						return $this->dropTable ($table, $database);
				}
			}
		} else if (! $this->get('security.context')->isGranted('ROLE_CONTRIBUTOR')) {
			throw $this->AccessDeniedException ($this->get('translator')->trans("Access Denied!"));
		} else {
			return $this->showDatasources($dsid, $table);
		}
	}

	protected function showDatasources($dsid, $table = null, $action = 'show') {
		$dbname = null;
		$datasources = array();
		$dss = $this->datasources->xpath("/DataSources/DataSource");
		foreach ($dss as $ds) {
			$ds_id = (string)$ds['id'];
			$dstype = (string)$ds['type'];
			$dsname = (string)$ds['name'];
			if ($dstype == 'internal' || $dstype == 'database') {
				$dsdatabase = (string)$ds['database'];
				$db = $this->datasources->xpath("/DataSources/Databases/Database[@id='".$dsdatabase."']")[0];
				$id = (string)$db['id'];
				$type = (string)$db['type'];
				$name = (string)$db['name'];
				$label = (string)$db['label'];
				if ($type == 'sqlite') {
					if (preg_match('/^(.*)\.db$/',$name, $matches) && file_exists($this->db_dir.'/'.$name)) {
						$datasources[] = array(
							'id' => $ds_id,
							'type' => $dstype,
							'name' => $dsname,
							'database' => array('id' => $id, 'type' => $type, 'name' => $name, 'label' => $label)
						);
						
					}
				} elseif ($type == 'jsonsql') {
					if (file_exists($this->db_dir.'/'.$name.".schema.json") && file_exists($this->db_dir.'/'.$name.".json")) {
						$datasources[] = array(
							'id' => $ds_id,
							'type' => $dstype,
							'name' => $dsname,
							'database' => array('id' => $id, 'type' => $type, 'name' => $name, 'label' => $label)
						);
					}
				} else {
					$host = (string)$db['host'];
					$port = (string)$db['port'];
					$user = (string)$db['user'];
					$password = (string)$db['password'];
					$datasources[] = array(
						'id' => $ds_id,
						'type' => $dstype,
						'name' => $dsname,
						'database' => array(
							'id' => $id, 'type' => $type, 'name' => $name, 'label' => $label, 
							'host' => $host, 'port' => $port, 'user' => $user, 'password' => $password
						)
					);
				}
			} elseif ($dstype == 'uri') {
				$dsuri = (string)$ds['uri'];
				$datasources[] = array(
					'id' => $ds_id,
					'type' => $dstype,
					'name' => $dsname,
					'uri' => $dsuri
				);
			}
		}
		$datasource = array();
		$tabledef = array();
		$tables = array();
		$tableinfos = array();
		$tabledatas = array();
		$dbname = '';
		if ($dsid !== null) {
			if ($dsid == 0) {
				$type = 'jsonsql';
				if ($this->get('kernel')->getContainer()->hasParameter('database_driver')) {
					switch ($this->get('kernel')->getContainer()->getParameter('database_driver')) {
						case 'pdo_sqlite':
							$type = 'sqlite';
							break;
						case 'pdo_mysql':
							$type = 'mysqli';
							break;
						case 'pdo_pgsql':
							$type = 'pgsql';
							break;
					}
				}
				if ($action == 'import') {
					$datasource = array(
						'action' => 'import',
						'id' => 0,
						'type' => 'internal',
						'name' => 'Import Datasource',
						'label' => 'Import Datasource',
						'uri' => '',
						'description' => '',
					);
				} else {
					$datasource = array(
						'action' => 'create',
						'id' => 0,
						'type' => 'internal',
						'name' => 'New Datasource',
						'label' => 'New Datasource',
						'database' => array(
							'id' => 0, 
							'type' => $type, 
							'name' => '', 
							'label' => '', 
							'host' => $this->get('kernel')->getContainer()->hasParameter('database_host') ? $this->get('kernel')->getContainer()->getParameter('database_host') : '', 
							'port' => $this->get('kernel')->getContainer()->hasParameter('database_port') ? $this->get('kernel')->getContainer()->getParameter('database_port') : '',
							'user' => $this->get('kernel')->getContainer()->hasParameter('database_user') ? $this->get('kernel')->getContainer()->getParameter('database_user') : '', 
							'password' => ''
						),
						'uri' => '',
						'description' => '',
					);
				}
			} else {
				$dss = $this->datasources->xpath("/DataSources/DataSource[@id='".$dsid."']");
				$datasource = array(
					'action' => $action == 'edit-table' ? 'show' : $action,
					'id' => (int)$dss[0]['id'],
					'type' => (string)$dss[0]['type'],
					'name' => (string)$dss[0]['name'],
					'label' => (string)$dss[0]['name'],
					'database' => array(
						'id' => (int)$dss[0]['database'], 'type' => '', 'name' => '', 'label' => '', 
						'host' => '', 'port' => 0, 'user' => '', 'password' => ''
					),
					'uri' => (string)$dss[0]['uri'],
					'description' => (string)$dss[0]->Description,
				);
				if ($datasource['type'] == 'internal' || $datasource['type'] == 'database') {
					$database = $this->getDatabase($dsid);
					$dbname = $database->getName();
					$datasource['label'] = $database->getLabel();
					$datasource['database']['id'] = $database->getId();
					$datasource['database']['type'] = $database->getType();
					$datasource['database']['name'] = $database->getName();
					$datasource['database']['label'] = $database->getLabel();
					$datasource['database']['host'] = $database->getHost();
					$datasource['database']['port'] = $database->getPort();
					$datasource['database']['user'] = $database->getUser();
					$datasource['database']['password'] = $database->getPassword();
					if ($datasource['type'] == 'internal' && $table !== null && $table != 'dummy') {
						$tabledef['action'] = $table != 'new' ? $action : 'create-table';
						$tabledef['name'] = $table;
						$tabledef['label'] = 'New Table';
						$tabledef['description'] = '';
						if ($table != 'new') {
							$tableinfos = $this->tableInfos($database, $table);
							foreach($tableinfos as $i => $info) {
								$dss = $this->datasources->xpath("/DataSources/DataSource[@type='internal' and @database='".$database->getId()."']");
								$column = null;
								foreach ($dss[0]->children() as $child) {
									if ($child->getName() == 'Table' && strcasecmp((string)$child['name'], $table) == 0) {
										foreach ($child->children() as $grandson) {
											if ($grandson->getName() == 'Column' && strcasecmp((string)$grandson['name'], $info['name']) == 0) {
												$column = $grandson;
												break;
											}
										}
										break;
									}
								}
								$tableinfos[$i]['g6k_type'] = ($column != null) ? (string)$column['type'] : $info['type'];
								$tableinfos[$i]['label'] = ($column != null) ? (string)$column['label'] : $info['name'];
								$tableinfos[$i]['description'] = ($column != null) ? (string)$column->Description : '';
								if ($tableinfos[$i]['g6k_type'] == 'choice' && $column != null && $column->Choices) {
									if ($column->Choices->Source) {
										$source = $column->Choices->Source;
										$result = $this->processSource($source);
										$choices = $this->getChoicesFromSource($source, $result);
										$tableinfos[$i]['choicesource']['id'] = (int)$source['id'];
										$tableinfos[$i]['choicesource']['datasource'] = (string)$source['datasource'];
										$tableinfos[$i]['choicesource']['request'] = (string)$source['request'];
										$tableinfos[$i]['choicesource']['returnType'] = (string)$source['returnType'];
										$tableinfos[$i]['choicesource']['separator'] = (string)$source['separator'];
										$tableinfos[$i]['choicesource']['delimiter'] = (string)$source['delimiter'];
										$tableinfos[$i]['choicesource']['returnPath'] = (string)$source['returnPath'];
										$tableinfos[$i]['choicesource']['valueColumn'] = (string)$source['valueColumn'];
										$tableinfos[$i]['choicesource']['labelColumn'] = (string)$source['labelColumn'];
									} else {
										$choices = array();
										foreach ($column->Choices->Choice as $choice) {
											$choices[(string)$choice['value']] = (string)$choice['label'];
										}
									}
									$tableinfos[$i]['choices'] = $choices;
								}
							}
							$tabledatas = $database->query("SELECT * FROM ".$table);
							foreach($tabledatas as $r => $row) {
								$i = 0;
								foreach ($row as $c => $cell) {
									if ($tableinfos[$i]['g6k_type'] == 'date' && $cell !== null) {
										$date = $this->parseDate('Y-m-d', substr($cell, 0, 10));
										$tabledatas[$r][$c] = $date->format('d/m/Y');
									} elseif ($tableinfos[$i]['g6k_type'] == 'money' || $tableinfos[$i]['g6k_type'] == 'percent') {
										$tabledatas[$r][$c] = number_format ( (float) $cell, 2, ",", "" );
									} elseif ($tableinfos[$i]['g6k_type'] == 'number') {
										$tabledatas[$r][$c] = str_replace ( ".", ",", $cell);
									} elseif ($tableinfos[$i]['g6k_type'] == 'choice') {
										$tabledatas[$r][$c] = $tableinfos[$i]['choices'][$cell];
									}
									$i++;
								}
							}
						}
					}
					if ($datasource['type'] == 'internal') {
						$tables = $this->tablesList($database);
						foreach($tables as $i => $tbl) {
							$dss = $this->datasources->xpath("/DataSources/DataSource[@type='internal' and @database='".$database->getId()."']");
							$dstable = null;
							foreach ($dss[0]->children() as $child) {
								if ($child->getName() == 'Table' && strcasecmp((string)$child['name'], $tbl['name']) == 0) {
									$dstable = $child;
									break;
								}
							}
						
							$tables[$i]['label'] = ($dstable != null) ? (string)$dstable['label'] : $tbl['name'];
							$tables[$i]['description'] = ($dstable != null) ? (string)$dstable->Description : '';
							if ($table !== null && $tbl['name'] == $table) {
								$tabledef['label'] = $tables[$i]['label'];
								$tabledef['description'] = $tables[$i]['description'];
							}
						}
					}
				}
			}
		}
 		$hiddens = array();
		$hiddens['script'] = $this->script;
		$silex = new Application();
		$silex->register(new MobileDetectServiceProvider());
		try {
			return $this->render(
				'EUREKAG6KBundle:admin/pages:datasources.html.twig',
				array(
					'ua' => $silex["mobile_detect"],
					'path' => $this->request->getScheme().'://'.$this->request->getHttpHost(),
					'nav' => 'datasources',
					'datasource' => $datasource,
					'datasources' => $datasources,
					'dsid' => $dsid,
					'dbname' => $dbname,
					'tables' => $tables,
					'table' => $tabledef,
					'tableinfos' => $tableinfos,
					'tabledatas' => $tabledatas,
					'hiddens' => $hiddens
				)
			);
		} catch (\Exception $e) {
			echo $e->getMessage();
			throw $this->createNotFoundException($this->get('translator')->trans("This template does not exist"));
		}
	}

	protected function doImportDatasource($files) {
		$container = $this->get('kernel')->getContainer();
		$uploadDir = str_replace("\\", "/", $container->getParameter('g6k_upload_directory'));
		$name = '';
		$schemafile = '';
		$datafile = '';
		foreach ($files as $fieldname => $file) {
			if ($file && $file->isValid()) {
				$filePath = $uploadDir . "/" . $this->get('g6k.file_uploader')->upload($file);
				if ($fieldname == 'datasource-schema-file') {
					$schemafile = $filePath;
				} elseif ($fieldname == 'datasource-data-file') {
					$datafile = $filePath;
					$name = $file->getClientOriginalName();
					if (preg_match("/^(.+)\.json$/", $name, $m)) {
						$name = trim($m[1]);
					}
				}
			}
		}
		if ($name != '' && $schemafile != '' && $datafile != '') {
			$driver = $container->getParameter('database_driver');
			$parameters = array(
				'database_driver' => $driver
			);
			if ($driver != 'pdo_sqlite') {
				if ($container->hasParameter('database_host')) {
					$parameters['database_host'] = $container->getParameter('database_host');
				}
				if ($container->hasParameter('database_port')) {
					$parameters['database_port'] = $container->getParameter('database_port');
				}
				if ($container->hasParameter('database_user')) {
					$parameters['database_user'] = $container->getParameter('database_user');
				}
				if ($container->hasParameter('database_password')) {
					$parameters['database_password'] = $container->getParameter('database_password');
				}
			}
			$converter = new JSONToSQLConverter($parameters);
			$form = $converter->convert($name, $schemafile, $datafile);
			$datasource = $this->doCreateDatasource($form);
			$dom = $datasource->ownerDocument;
			$tableid = 1;
			foreach ($form['datasource-tables'] as $tbl) {
				$table = $dom->createElement("Table");
				$table->setAttribute('id', $tableid++);
				$table->setAttribute('name', $tbl['name']);
				$table->setAttribute('label', $tbl['label']);
				$descr = $dom->createElement("Description");
				$descr->appendChild($dom->createCDATASection($tbl['description']));
				$table->appendChild($descr);
				$columnid = 1;
				foreach ($tbl['columns'] as $col) {
					$column = $dom->createElement("Column");
					$column->setAttribute('id', $columnid++);
					$column->setAttribute('name', $col['name']);
					$column->setAttribute('type', $col['type']);
					$column->setAttribute('label', $col['label']);
					$descr = $dom->createElement("Description");
					$descr->appendChild($dom->createCDATASection($col['description']));
					$column->appendChild($descr);
					if (isset($col['choices'])) {
						$choices = $dom->createElement("Choices");
						$choiceid = 1;
						foreach ($col['choices'] as $ch) {
							$choice = $dom->createElement("Choice");
							$choice->setAttribute('id', $choiceid++);
							$choice->setAttribute('value', $ch['value']);
							$choice->setAttribute('label', $ch['label']);
							$choices->appendChild($choice);
						}
						$column->appendChild($choices);
					} elseif (isset($col['source'])) {
						$choices = $dom->createElement("Choices");
						$source = $dom->createElement("Source");
						$source->setAttribute('id', 1);
						$source->setAttribute('datasource', $col['source']['datasource']);
						if (isset($col['source']['request'])) {
							$source->setAttribute('request', $col['source']['request']);
						}
						$source->setAttribute('returnType', $col['source']['returnType']);
						if (isset($col['source']['returnPath'])) {
							$source->setAttribute('returnPath', $col['source']['returnPath']);
						}
						$source->setAttribute('valueColumn', $col['source']['valueColumn']);
						$source->setAttribute('labelColumn', $col['source']['labelColumn']);
						$choices->appendChild($source);
						$column->appendChild($choices);
					}
					$table->appendChild($column);
				}
				$datasource->appendChild($table);
			}
			$this->saveDatasources($dom);
		}
		if ($schemafile != '') {
			unlink($schemafile);
		}
		if ($datafile != '') {
			unlink($datafile);
		}
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasource', array('dsid' => $datasource->getAttribute('id'))));
	}

	protected function getChoicesFromSource($source, $result) {
		$choices = array();
		if ($result !== null) {
			switch ((string)$source['returnType']) {
				case 'json':
					$valueColumn = (string)$source['valueColumn'];
					if (is_numeric($valueColumn)) {
						$valueColumn = (int)$valueColumn - 1;
					}
					$labelColumn = (string)$source['labelColumn'];
					if (is_numeric($labelColumn)) {
						$labelColumn = (int)$labelColumn - 1;
					}
					foreach ($result as $row) {
						$choices[$row[$valueColumn]] =  $row[$labelColumn];
					}
					break;
				case 'xml':
					$valueColumn = (string)$source['valueColumn'];
					$labelColumn = (string)$source['labelColumn'];
					foreach ($result as $row) {
						if (preg_match("/^@(.+)$", $valueColumn, $m1)) {
							if (preg_match("/^@(.+)$", $labelColumn, $m2)) {
								$choices[(string)$row[$m1[1]]] = (string)$row[$m2[1]];
							} else {
								$choices[(string)$row[$m1[1]]] = $row->$labelColumn;
							}
						} elseif (preg_match("/^@(.+)$", $labelColumn, $m2)) {
							$choices[$row->$valueColumn] = (string)$row[$m2[1]];
						} else {
							$choices[$row->$valueColumn] = $row->$labelColumn;
						}
					}
					break;
				case 'assocArray':
					$valueColumn = strtolower((string)$source['valueColumn']);
					$labelColumn = strtolower((string)$source['labelColumn']);
					foreach ($result as $row) {
						$choices[$row[$valueColumn]] =  $row[$labelColumn];
					}
					break;
				case 'csv':
					$valueColumn = (int)$source['valueColumn'] - 1;
					$labelColumn = (int)$source['labelColumn'] - 1;
					foreach ($result as $row) {
						$choices[$row[$valueColumn]] =  $row[$labelColumn];
					}
					break;
			}
		}
		return $choices;
	}

	protected function processSource($source) {
		$ds = (string)$source['datasource'];
		if (is_numeric($ds)) {
			$datasources = $this->datasources->xpath("/DataSources/DataSource[@id='".$ds."']");
		} else {
			$datasources = $this->datasources->xpath("/DataSources/DataSource[@name='".$ds."']");
		}
		switch ((string)$datasources[0]['type']) {
			case 'uri':
				$uri = (string)$datasources[0]['uri'];
				$client = Client::createClient();
				$data = array(); // TODO : add parameters elements in DataSources.xsd
				if ((string)$datasources[0]['method'] == "" || (string)$datasources[0]['method'] == "GET") {
					$result = $client->get($uri);
				} else {
					$result = $client->post($uri, $data);
				}
				break;
			case 'database':
			case 'internal':
				$databases = $this->datasources->xpath("/DataSources/Databases/Database[@id='".(string)$datasources[0]['database']."']");
				$database = new Database(null, (int)$databases[0]['id'], (string)$databases[0]['type'], (string)$databases[0]['name']);
				if ((string)$databases[0]['host'] != "") {
					$database->setHost((string)$databases[0]['host']);
				}
				if ((string)$databases[0]['port'] != "") {
					$database->setPort((int)$databases[0]['port']);
				}
				if ((string)$databases[0]['user'] != "") {
					$database->setUser((string)$databases[0]['user']);
				}
				if ((string)$databases[0]['password'] != "") {
					$database->setPassword((string)$databases[0]['password']);
				} elseif ((string)$databases[0]['user'] != "") {
					try {
						$host = $this->get('kernel')->getContainer()->getParameter('database_host');
						$port = $this->get('kernel')->getContainer()->getParameter('database_port');
						$user = $this->get('kernel')->getContainer()->getParameter('database_user');
						if ((string)$databases[0]['host'] == $host && (string)$databases[0]['port'] == $port && (string)$databases[0]['user'] == $user) {
							$database->setPassword($this->get('kernel')->getContainer()->getParameter('database_password'));
						}
					} catch (\Exception $e) {
					}
				}
				$query = (string)$source['request'];
				$database->connect();
				$result = $database->query($query);
				break;
		}
		switch ((string)$source['returnType']) {
			case 'json':
				$json = json_decode($result, true);
				return ResultFilter::filter("json", $json, (string)$source['returnPath']);
			case 'assocArray':
				return $this->filterResultByLines($result, (string)$source['returnPath']);
			case 'xml':
				return ResultFilter::filter("xml", $result, (string)$source['returnPath']);
			case 'csv':
				$result = ResultFilter::filter("csv", $result, "", null, (string)$source['separator'], (string)$source['delimiter']);
				return $this->filterResultByLines($result, (string)$source['returnPath']);
		}
		return null;
	}

	protected function filterResultByLines($result, $filter) {
		if ($filter == '') {
			return $result;
		}
		$filtered = array();
		$ranges = explode("/", $filter);
		$len = count($result);
		foreach ($ranges as $range) {
			$lines = explode("-", trim($range));
			if (count($lines) == 1) {
				$line = (int)trim($lines[0]) - 1;
				if ($line >= 0 && $line < $len) {
					$filtered[] = $result[$line];
				}
			} elseif (count($lines) == 2) {
				$from = max(0, (int)trim($lines[0]) - 1);
				$to = (int)trim($lines[1]) - 1;
				if ($from <= $to) {
					for ($i = $from; $i <= $to && $i < $len; $i++) {
						$filtered[] = $result[$i];
					}
				}
			}
		}
		return $filtered;
	}

	protected function getDatabase($dsid, $withDbName = true) {
		$datasources = $this->datasources->xpath("/DataSources/DataSource[@id='".$dsid."']");
		$dbid = (int)$datasources[0]['database'];
		$databases = $this->datasources->xpath("/DataSources/Databases/Database[@id='".$dbid."']");
		$dbtype = (string)$databases[0]['type'];
		$dbname = (string)$databases[0]['name'];
		$database = new Database(null, $dbid, $dbtype, $dbname);
		if ((string)$databases[0]['label'] != "") {
			$database->setLabel((string)$databases[0]['label']);
		} else {
			$database->setLabel($dbname);
		}
		if ((string)$databases[0]['host'] != "") {
			$database->setHost((string)$databases[0]['host']);
		}
		if ((string)$databases[0]['port'] != "") {
			$database->setPort((int)$databases[0]['port']);
		}
		if ((string)$databases[0]['user'] != "") {
			$database->setUser((string)$databases[0]['user']);
		}
		if ((string)$databases[0]['password'] != "") {
			$database->setPassword((string)$databases[0]['password']);
		} elseif ((string)$databases[0]['user'] != "") {
			try {
				$user = $this->get('kernel')->getContainer()->getParameter('database_user');
				if ((string)$databases[0]['user'] == $user) {
					$database->setPassword($this->get('kernel')->getContainer()->getParameter('database_password'));
				}
			} catch (\Exception $e) {
			}
		}
		$database->connect($withDbName);
		return $database;
	}

	protected function checkValue($name, $info, $value) {
		if ($value === null || $value == '') {
			if ($info['notnull'] == 1) { 
				return sprintf("The field '%s' is required", $info['label']);
			} else {
				return true;
			}
		}
		switch ($info['g6k_type']) {
			case 'date':
				if (! preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $value)) {
					return sprintf("The field '%s' is not a valid date", $info['label']);
				}
				break;
			case 'boolean':
				if ( ! in_array($value, array('0', '1', 'false', 'true'))) {
					return sprintf("The field '%s' is invalid", $info['label']);
				}
				break;
			case 'number': 
				$value = str_replace(",", ".", $value);
				if (! is_numeric($value)) {
					return sprintf("The field '%s' is not a number", $info['label']);
				}
				break;
			case 'integer': 
				if (! ctype_digit ( $value )) {
					return sprintf("The field '%s' is not a number", $info['label']);
				}
				break;
			case 'day': 
				if (! ctype_digit ( $value ) || (int)$value > 31) {
					return sprintf("The field '%s' is invalid", $info['label']);
				}
				break;
			case 'month': 
				if (! ctype_digit ( $value ) || (int)$value > 12 ) {
					return sprintf("The field '%s' is invalid", $info['label']);
				}
				break;
			case 'year': 
				if (! ctype_digit ( $value ) || strlen($value) != 4 ) {
					return sprintf("The field '%s' is not a valid year", $info['label']);
				}
				break;
			case 'text': 
			case 'textarea': 
				break;
			case 'money': 
				$value = str_replace(",", ".", $value);
				if (! preg_match("/^\d+(\.\d{1,2})?$/", $value)) {
					return sprintf("The field '%s' is not a valid currency", $info['label']);
				}
				break;
			case 'choice':
				foreach ($info['choices'] as $val => $label) {
					if ($value == $val) {
						return true;
					}
				}
				return sprintf("The field '%s' is invalid", $info['label']);
			case 'percent':
				$value = str_replace(",", ".", $value);
				if (! is_numeric($value)) {
					return sprintf("The field '%s' is not numeric", $info['label']);
				}
				break;
		}
		return true;
	}

	protected function tablesList($database) {
		switch ($database->getType()) {
			case 'jsonsql':
				$tableslist = array();
				foreach($database->getConnection()->schema()->properties as $tbl => $prop) {
					$tableslist[] = array(
						'type' => 'table',
						'name' => $tbl,
						'tbl_name' => $tbl
					);
				}
				break;
			case 'sqlite':
				$tableslist =  $database->query("SELECT * FROM sqlite_master WHERE type='table' AND tbl_name NOT LIKE 'sqlite_%'");
				break;
			case 'pgsql':
				$tableslist = $database->query("SELECT 'table' as type, table_name as name, table_name as tbl_name FROM information_schema.tables where table_schema = 'public' and table_type = 'BASE TABLE' and table_name != 'fos_user'");
				break;
			case 'mysql':
			case 'mysqli':
				$dbname = str_replace('-', '_', $database->getName());
				$tableslist = $database->query("SELECT 'table' as type, table_name as name, table_name as tbl_name FROM information_schema.tables where table_schema = '$dbname' and table_name != 'fos_user';");
				break;
			default:
				$tableslist = null;
		}
		return $tableslist;
	}

	protected function tableInfos($database, $table) {
		switch ($database->getType()) {
			case 'jsonsql':
				$tableinfos = array();
				$cid = 0;
				foreach($database->getConnection()->schema()->properties->{$table}->items->properties as $name => $column) {
					$notnull = in_array($name, $database->getConnection()->schema()->properties->{$table}->items->required);
					$tableinfos[] = array(
						'cid' => ++$cid,
						'name' => $name,
						'type' => strtoupper($column->type),
						'notnull' => $notnull ? 1 : 0,
						'dflt_value' => isset($column->default) ? $column->default : ''
					);
				}
				break;
			case 'sqlite':
				$tableinfos = $database->query("PRAGMA table_info('".$table."')");
				break;
			case 'pgsql':
				$tableinfos = $database->query("SELECT ordinal_position as cid, column_name as name, data_type as type, is_nullable, column_default as dflt_value FROM information_schema.columns where table_name = '$table' order by ordinal_position");
				foreach($tableinfos as &$info) {
					$info['notnull'] = $info['is_nullable'] == 'NO' ? 1 : 0;
				}
				break;
			case 'mysql':
			case 'mysqli':
				$dbname = str_replace('-', '_', $database->getName());
				$tableinfos = $database->query("SELECT ordinal_position as cid, column_name as name, data_type as type, is_nullable, column_default as dflt_value, column_key FROM information_schema.columns where table_schema = '$dbname' and table_name = '$table' order by ordinal_position");
				foreach($tableinfos as &$info) {
					$info['notnull'] = $info['is_nullable'] == 'NO' ? 1 : 0;
					$info['pk'] = $info['column_key'] == 'PRI' ? 1 : 0;
				}
				break;
			default:
				$tableinfos = null;
		}
		return $tableinfos;
	}

	protected function infosColumns($database, $table) {
		$infosColumns = array();
		$tableinfos = $this->tableInfos($database, $table);
		foreach($tableinfos as $i => $info) {
			$infosColumns[$info['name']]['notnull'] = $info['notnull'];
			$infosColumns[$info['name']]['dflt_value'] = $info['dflt_value'];
			$datasources = $this->datasources->xpath("/DataSources/DataSource[@type='internal' and @database='".$database->getId()."']");
			$column = null;
			foreach ($datasources[0]->children() as $child) {
				if ($child->getName() == 'Table' && strcasecmp((string)$child['name'], $table) == 0) {
					foreach ($child->children() as $grandson) {
						if ($grandson->getName() == 'Column' && strcasecmp((string)$grandson['name'], $info['name']) == 0) {
							$column = $grandson;
							break;
						}
					}
					break;
				}
			}
			$infosColumns[$info['name']]['g6k_type'] = ($column != null) ? (string)$column['type'] : $info['type'];
			$infosColumns[$info['name']]['type'] = $info['type'];
			$infosColumns[$info['name']]['label'] = ($column != null) ? (string)$column['label'] : $info['name'];
			$infosColumns[$info['name']]['description'] = ($column != null) ? (string)$column->Description : '';
			if ($infosColumns[$info['name']]['g6k_type'] == 'choice' && $column != null && $column->Choices) {
				if ($column->Choices->Source) {
					$source = $column->Choices->Source;
					$infosColumns[$info['name']]['choicesource']['datasource'] = (string)$source['datasource'];
					$infosColumns[$info['name']]['choicesource']['returnType'] = (string)$source['returnType'];
					$infosColumns[$info['name']]['choicesource']['request'] = (string)$source['request'];
					$infosColumns[$info['name']]['choicesource']['valueColumn'] = (string)$source['valueColumn'];
					$infosColumns[$info['name']]['choicesource']['labelColumn'] = (string)$source['labelColumn'];
					$infosColumns[$info['name']]['choicesource']['returnPath'] = (string)$source['returnPath'];
					$infosColumns[$info['name']]['choicesource']['separator'] = (string)$source['separator'];
					$infosColumns[$info['name']]['choicesource']['delimiter'] = (string)$source['delimiter'];
					$result = $this->processSource($source);
					$choices = $this->getChoicesFromSource($source, $result);
				} else {
					$choices = array();
					foreach ($column->Choices->Choice as $choice) {
						$choices[(string)$choice['value']] = (string)$choice['label'];
					}
				}
				$infosColumns[$info['name']]['choices'] = $choices;
			}
		}
		return $infosColumns;
	}

	protected function infosColumnsToForm($table, $infosColumns) {
		$fields = array();
		$types = array();
		$notnulls = array();
		$defaults = array();
		foreach($infosColumns as $name => $info) {
			if ($name != 'id') {
				$fields[] = $name;
				$types[] = $info['g6k_type'];
				$notnulls[] = $info['notnull'];
				$defaults[] = $info['dflt_value'];
			}
		}
		return array(
			'table-name' => $table,
			'field' => $fields,
			'type' => $types,
			'notnull' => $notnulls,
			'default' => $defaults,
		);
	}

	protected function doCreateDatasource ($form) {
		$dom = dom_import_simplexml($this->datasources)->ownerDocument;
		$xpath = new \DOMXPath($dom);
		$dss = $xpath->query("/DataSources");
		$dbs = $xpath->query("/DataSources/Databases");
		$type = $form['datasource-type'];
		$ds = $dss->item(0)->getElementsByTagName('DataSource');
		$len = $ds->length;
		$maxId = 0;
		for($i = 0; $i < $len; $i++) {
			$id = (int)$ds->item($i)->getAttribute('id');
			if ($id > $maxId) {
				$maxId = $id;
			}
		}
		$datasource = $dom->createElement("DataSource");
		$datasource->setAttribute('id', $maxId + 1);
		$datasource->setAttribute('type', $type);
		$datasource->setAttribute('name', $form['datasource-name']);
		$descr = $dom->createElement("Description");
		$descr->appendChild($dom->createCDATASection(preg_replace("/(\<br\>)+$/", "", $form['datasource-description'])));
		$datasource->appendChild($descr);
		switch($type) {
			case 'internal':
			case 'database':
				$db = $dbs->item(0)->getElementsByTagName('Database');
				$len = $db->length;
				$maxId = 0;
				for($i = 0; $i < $len; $i++) {
					$id = (int)$db->item($i)->getAttribute('id');
					if ($id > $maxId) {
						$maxId = $id;
					}
				}
				$dbtype = $form['datasource-database-type'];
				$dbname = $form['datasource-database-name'];
				if ($dbtype == 'sqlite' && ! preg_match("/\.db$/", $dbname)) {
					$dbname .= '.db';
				}
				$database = $dom->createElement("Database");
				$database->setAttribute('id', $maxId + 1);
				$database->setAttribute('type', $dbtype);
				$database->setAttribute('name', $dbname);
				$database->setAttribute('label', $form['datasource-database-label']);
				if ($dbtype == 'mysqli' || $dbtype == 'pgsql') {
					$database->setAttribute('host', $form['datasource-database-host']);
					$database->setAttribute('port', $form['datasource-database-port']);
					$database->setAttribute('user', $form['datasource-database-user']);
					if (isset($form['datasource-database-password'])) {
						$database->setAttribute('password', $form['datasource-database-password']);
					}
				}
				$dbs->item(0)->appendChild($database);
				$datasource->setAttribute('database', $database->getAttribute('id'));
				break;
			case 'uri':
				$datasource->setAttribute('name', $form['datasource-name']);
				$datasource->setAttribute('uri', $form['datasource-uri']);
				$datasource->setAttribute('method', $form['datasource-method']);
				break;
		}
		$dss->item(0)->insertBefore($datasource, $dbs->item(0));
		return $datasource;
	}

	protected function createDatasource($form) {
		$datasource = $this->doCreateDatasource($form);
		$this->saveDatasources($datasource->ownerDocument);
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasource', array('dsid' => $datasource->getAttribute('id'))));
	}

	protected function migrateDB($dsid, $dbtype, $fromDatabase) {
		$datasource = $this->datasources->xpath("/DataSources/DataSource[@id='".$dsid."']")[0];
		try {
			if ($dbtype == 'jsonsql' || $dbtype == 'sqlite') {
				$database = $this->getDatabase($dsid);
			} else {
				$database = $this->getDatabase($dsid, false);
			}
		} catch (Exception $e) {
			return "Can't get database : " . $e->getMessage();
		}
		switch ($database->getType()) {
			case 'pgsql':
				$dbschema = str_replace('-', '_', $database->getName());
				try {
					$database->exec("CREATE DATABASE " . $dbschema. " encoding 'UTF8'");
					$database->setConnected(false);
					$database->connect();
				} catch (Exception $e) {
					return "Can't create database $dbschema : " . $e->getMessage();
				}
				break;
			case 'mysql':
			case 'mysqli':
				$dbschema = str_replace('-', '_', $database->getName());
				try {
					$database->exec("CREATE DATABASE IF NOT EXISTS " . $dbschema . " character set utf8");
					$database->setConnected(false);
					$database->connect();
				} catch (Exception $e) {
					return "Can't create database $dbschema : " . $e->getMessage();
				}
				break;
		}
		foreach ($datasource->children() as $child) {
			if ($child->getName() == 'Table') {
				$table = (string)$child['name'];
				$infosColumns = $this->infosColumns($fromDatabase, $table);
				$form = $this->infosColumnsToForm($table, $infosColumns);
				if (($result = $this->createDBTable($form, $database)) !== true) {
					return $result;
				}
				$fields = implode(", ", $form['field']);
				$rows = $fromDatabase->query("select ". $fields . " from " . $table . " order by id");
				foreach ($rows as $row) {
					$values = array();
					foreach ($row as $name => $value) {
						$info = $infosColumns[$name];
						if ($value === null || $value == '') {
							$values[] = "NULL";
						} else if ( $info['g6k_type'] == 'text' || $info['g6k_type'] == 'date' || preg_match("/^(text|char|varchar)/i", $info['type'])) {
							$values[] = $database->quote($value);
						} else  {
							$values[] = str_replace(",", ".", $value);
						}
					}
					$insert = "INSERT INTO " . $table . " (" . $fields . ") values (" . implode(", ", $values) . ")";
					try {
						$database->exec($insert);
					} catch (Exception $e) {
						return "Can't insert to $table of database $dbschema : " . $e->getMessage();
					}
				}
			}
		}
		if ($database->gettype() == 'jsonsql') {
			$database->getConnection()->commit();
		}
		return true;
	}

	protected function createDBTable($form, $database) {
		$create = "create table " . $form['table-name'] . " (\n";
		if (!in_array('id', $form['field'])) {
			switch ($database->getType()) {
				case 'jsonsql':
					$create .= "id integer not null primary key autoincrement,\n";
					break;
				case 'sqlite':
					$create .= "id INTEGER not null primary key autoincrement,\n";
					break;
				case 'pgsql':
					$create .= "id serial primary key,\n";
					break;
				case 'mysql':
				case 'mysqli':
					$create .= "id INT not null primary key auto_increment,\n";
					break;
			}
		}
		foreach ($form['field'] as $i => $field) {
			if ($database->getType() == 'jsonsql') {
				$create .= $field . " " . $form['type'][$i];
			} else {
				$create .= $field . " " . $this->datatypes[$database->getType()][$form['type'][$i]];
			}
			if ($form['notnull'][$i] == 1) {
				$create .= " not null";
			}
			if ($database->getType() =='jsonsql' && $form['label'][$i] != '') {
				$create .= " title " . $database->quote($form['label'][$i]);
			}
			if ($database->getType() =='jsonsql' && $form['description'][$i] != '') {
				$create .= " comment " . $database->quote($form['description'][$i]);
			}
			if ($i < count($form['field']) - 1 ) {
				$create .= ",";
			}
			$create .= "\n";
		}
		$create .= ")";
		try {
			$database->exec($create);
			if ($form['table-label'] != '' && $database->getType() == 'jsonsql') {
				$alter = "alter table " . $form['table-name'] . " modify title  " . $database->quote($form['table-label']);
				$database->exec($alter);
			}
			if ($form['table-description'] != '' && $database->getType() == 'jsonsql') {
				$alter = "alter table " . $form['table-name'] . " modify comment  " . $database->quote($form['table-description']);
				$database->exec($alter);
			}
		} catch (Exception $e) {
			return "Can't create {$form['table-name']} : " . $e->getMessage();
		}
		return true;
	}

	protected function editDBTable($form, $table, $database) {
		$infosColumns = $this->infosColumns($database, $table);
		if (strcasecmp($form['table-name'], $table) != 0) {
			$rename = "ALTER TABLE $table RENAME TO {$form['table-name']}";
			try {
				$database->exec($rename);
			} catch (Exception $e) {
				return "Can't rename table $table : " . $e->getMessage();
			}
		}
		$col = 0;
		foreach($infosColumns as $name => $info) {
			if (strcasecmp($form['field'][$col], $name) != 0 && $database->getType() != 'sqlite') {
				$rename = "";
				switch ($database->getType()) {
					case 'mysql':
					case 'mysqli':
						$rename = "ALTER TABLE $table CHANGE COLUMN $name {$form['field'][$col]}";
						break;
					case 'jsonsql':
					case 'pgsql':
						$rename = "ALTER TABLE $table RENAME COLUMN $name TO {$form['field'][$col]}";
						break;
				}
				try {
					$database->exec($rename);
				} catch (Exception $e) {
					return "Can't rename column $name of table $table : " . $e->getMessage();
				}
			}
			if ($form['type'][$col] != $info['g6k_type'] && $database->getType() != 'sqlite') {
				$changetype = "";
				if ($database->getType() == 'jsonsql') {
					$changetype = "ALTER TABLE $table MODIFY COLUMN $name SET TYPE {$form['type'][$col]}";
					try {
						$database->exec($changetype);
					} catch (Exception $e) {
						return "Can't modify type of column $name of table $table : " . $e->getMessage();
					}
				} else {
					$newDBType = $this->datatypes[$database->getType()][$form['type'][$col]];
					if ($info['type'] != $newDBType) {
						switch ($database->getType()) {
							case 'mysql':
							case 'mysqli':
								$changetype = "ALTER TABLE $table MODIFY COLUMN $name $newDBType";
								break;
							case 'pgsql':
								$changetype = "ALTER TABLE $table ALTER COLUMN $name SET DATA TYPE $newDBType";
								break;
						}
						try {
							$database->exec($changetype);
						} catch (Exception $e) {
							return "Can't modify type of column $name of table $table : " . $e->getMessage();
						}
					}
				}
			}
			if ($form['notnull'][$col] != $info['notnull'] && $database->getType() != 'sqlite') {
				$changenullable = "";
				switch ($database->getType()) {
					case 'jsonsql':
						if ($form['notnull'][$col] == 1) {
							$changenullable = "ALTER TABLE $table MODIFY COLUMN $name SET NOT NULL";
						} else {
							$changenullable = "ALTER TABLE $table MODIFY COLUMN $name REMOVE NOT NULL";
						}
						break;
					case 'mysql':
					case 'mysqli':
						$newDBType = $this->datatypes[$database->getType()][$form['type'][$col]];
						$newNullable = $form['notnull'][$col] == 1 ? 'NOT NULL' : 'NULL';
						$changenullable = "ALTER TABLE $table MODIFY COLUMN $name $newDBType $newNullable";
						break;
					case 'pgsql':
						if ($form['notnull'][$col] == 1) {
							$changenullable = "ALTER TABLE $table ALTER COLUMN $name SET NOT NULL";
						} else {
							$changenullable = "ALTER TABLE $table ALTER COLUMN $name DROP NOT NULL";
						}
						break;
				}
				try {
					$database->exec($changenullable);
				} catch (Exception $e) {
					return "Can't alter 'NOT NULL' property of column $name of table $table : " . $e->getMessage();
				}
			}
			if ($form['label'][$col] != $info['label'] && $database->getType() == 'jsonsql') {
				$changelabel = "ALTER TABLE $table MODIFY COLUMN $name SET TITLE " . $database->quote($form['label'][$col]);
				try {
					$database->exec($changelabel);
				} catch (Exception $e) {
					return "Can't modify title of column $name of table $table : " . $e->getMessage();
				}
			}
			if ($form['description'][$col] != $info['description'] && $database->getType() == 'jsonsql') {
				$changedescription = "ALTER TABLE $table MODIFY COLUMN $name SET COMMENT " . $database->quote($form['description'][$col]);
				try {
					$database->exec($changedescription);
				} catch (Exception $e) {
					return "Can't modify description of column $name of table $table : " . $e->getMessage();
				}
			}
			$col++;
		}
		for ($i = $col; $i < count($form['field']); $i++) {
			$name = $form['field'][$i];
			$type = $form['type'][$i];
			$label = $form['label'][$i];
			$description = $form['description'][$i];
			$notnull = $form['notnull'][$i] == 1 ? 'NOT NULL' : '';
			$dbype = $this->datatypes[$database->getType()][$type];
			$addcolumn = "";
			switch ($database->getType()) {
				case 'jsonsql':
					$addcolumn = "ALTER TABLE $table ADD COLUMN $name $type $notnull TITLE " . $database->quote($label) . " COMMENT " . $database->quote($description);
					break;
				case 'sqlite':
					$addcolumn = "ALTER TABLE $table ADD COLUMN $name $dbype $notnull";
					break;
				case 'mysql':
				case 'mysqli':
					$addcolumn = "ALTER TABLE $table ADD COLUMN $name $dbype $notnull";
					break;
				case 'pgsql':
					$addcolumn = "ALTER TABLE $table ADD COLUMN $name $dbype $notnull";
					break;
			}
			try {
				$database->exec($addcolumn);
			} catch (Exception $e) {
				return "Can't add the column '$name' into table '$table' : " . $e->getMessage();
			}
		}
		return true;
	}

	protected function addDBTableRow($form, $table, $database) {
		$infosColumns = $this->infosColumns($database, $table);
		$insertNames = array();
		$insertValues = array();
		foreach($infosColumns as $name => $info) {
			$value = isset($form[$name]) ? $form[$name] : null;
			if (($check = $this->checkValue($name, $info, $value)) !== true) {
				return $check;
			}
			if ($name != 'id') {
				$insertNames[] = $name;
				if ($value === null || $value == '') {
					$insertValues[] = "NULL";
				} else if ($info['g6k_type'] == 'date') {
					$insertValues[] = $database->quote($this->parseDate('d/m/Y', substr($value, 0, 10))->format('Y-m-d'));
				} else if ($info['g6k_type'] == 'multichoice') {
					$insertValues[] = $database->quote(json_encode($value));
				} else if ( $info['g6k_type'] == 'text' || preg_match("/^(text|char|varchar)/i", $info['type'])) {
					$insertValues[] = $database->quote($value);
				} else  {
					$insertValues[] = str_replace(",", ".", $value);
				}
			}
		}
		$sql = "INSERT INTO ".$table." (".implode(', ', $insertNames).") VALUES (".implode(', ', $insertValues).")";
		try {
			$database->exec($sql);
		} catch (Exception $e) {
			return "Can't insert to $table : " . $e->getMessage();
		}
		return true;
	}

	protected function updateDBTableRow($form, $table, $database) {
		$infosColumns = $this->infosColumns($database, $table);
		$updateFields = array();
		foreach($infosColumns as $name => $info) {
			$value = isset($form[$name]) ? $form[$name] : null;
			if (($check = $this->checkValue($name, $info, $value)) !== true) {
				return $check;
			}
			if ($name != 'id') {
				if ($value === null || $value == '') {
					$updateFields[] = $name . "=NULL";
				} else if ($info['g6k_type'] == 'date') {
					$updateFields[] = $name . "='" . $this->parseDate('d/m/Y', substr($value, 0, 10))->format('Y-m-d') . "'";
				} else if ($info['g6k_type'] == 'multichoice') {
					$updateFields[] = $name . "='" . $database->quote(json_encode($value)) . "'";
				} else if ( $info['g6k_type'] == 'text' || preg_match("/^(text|char|varchar)/i", $info['type'])) {
					$updateFields[] = $name . "=" . $database->quote($value);
				} else  {
					$value = str_replace(",", ".", $value);
					$updateFields[] = $name . "=" . $value;
				}
			}			
		}
		$sql = "UPDATE ".$table." SET ".implode(', ', $updateFields)." WHERE id=".$form['id'];
		try {
			$database->exec($sql);
		} catch (Exception $e) {
			return "Can't update $table : " . $e->getMessage();
		}
		return true;
	}

	protected function deleteDBTableRow($form, $table, $database) {
		try {
			$database->exec("DELETE FROM ".$table." WHERE id=".$form['id']);
		} catch (Exception $e) {
			return "Can't delete from $table : " . $e->getMessage();
		}
		return true;
	}

	protected function dropDBTable($table, $database) {
		try {
			$database->exec("DROP TABLE ".$table);
		} catch (Exception $e) {
			return "Can't drop $table : " . $e->getMessage();
		}
		return true;
	}

	protected function createTable($form, $database) {
		if (($result = $this->createDBTable($form, $database)) !== true) {
			return $this->errorResponse($form, $result);
		}
		$dom = dom_import_simplexml($this->datasources)->ownerDocument;
		$xpath = new \DOMXPath($dom);
		$datasource = $xpath->query("/DataSources/DataSource[@type='internal' and @database='".$database->getId()."']")->item(0);
		$tables = $datasource->getElementsByTagName('Table');
		$len = $tables->length;
		$maxId = 0;
		for($i = 0; $i < $len; $i++) {
			$id = (int)$tables->item($i)->getAttribute('id');
			if ($id > $maxId) {
				$maxId = $id;
			}
		}
		$newTable = $dom->createElement("Table");
		$newTable->setAttribute('id', ''.($maxId + 1));
		$newTable->setAttribute('name', $form['table-name']);
		$newTable->setAttribute('label', $form['table-label']);
		$descr = $dom->createElement("Description");
		$descr->appendChild($dom->createCDATASection(preg_replace("/(\<br\>)+$/", "", $form['table-description'])));
		$newTable->appendChild($descr);
		foreach ($form['field'] as $i => $field) {
			$column = $dom->createElement("Column");
			$column->setAttribute('id', $i + 1);
			$column->setAttribute('name', $field);
			$column->setAttribute('type', $form['type'][$i]);
			$column->setAttribute('label', $form['label'][$i]);
			$descr = $dom->createElement("Description");
			$descr->appendChild($dom->createCDATASection(preg_replace("/(\<br\>)+$/", "", $form['description'][$i])));
			$column->appendChild($descr);
			if ($form['type'][$i] == 'choice' || $form['type'][$i] == 'multichoice') {
				$choices = $dom->createElement("Choices");
				if (isset($form['field-'.$i.'-choicesource-datasource'])) {
					$source = $dom->createElement("Source");
					$source->setAttribute('id', 1);
					$source->setAttribute('datasource', $form['field-'.$i.'-choicesource-datasource']);
					$source->setAttribute('returnType', $form['field-'.$i.'-choicesource-returnType']);
					$source->setAttribute('valueColumn', $form['field-'.$i.'-choicesource-valueColumn']);
					$source->setAttribute('labelColumn', $form['field-'.$i.'-choicesource-labelColumn']);
					if (($form['field-'.$i.'-choicesource-datasource'] == 'internal' || $form['field-'.$i.'-choicesource-datasource'] == 'database')) {
						$source->setAttribute('request', $form['field-'.$i.'-choicesource-request']);
					} else {
						if (isset($form['field-'.$i.'-choicesource-returnPath'])) {
							$source->setAttribute('returnPath', $form['field-'.$i.'-choicesource-returnPath']);
						}
						if ($form['field-'.$i.'-choicesource-returnType'] == 'csv') {
							if (isset($form['field-'.$i.'-choicesource-separator'])) {
								$source->setAttribute('separator', $form['field-'.$i.'-choicesource-separator']);
							}
							if (isset($form['field-'.$i.'-choicesource-delimiter'])) {
								$source->setAttribute('delimiter', $form['field-'.$i.'-choicesource-delimiter']);
							}
						}
					}
					$choices->appendChild($source);
				} else{
					foreach ($form['field-'.$i.'-choice-value'] as $c => $value) {
						$choice = $dom->createElement("Choice");
						$choice->setAttribute('id', $c + 1);
						$choice->setAttribute('value', $value);
						$choice->setAttribute('label', $form['field-'.$i.'-choice-label'][$c]);
						$choices->appendChild($choice);
					}
				}
				$column->appendChild($choices);
			}
			$newTable->appendChild($column);
		}
		$datasource->appendChild($newTable);
		$this->saveDatasources($dom);
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasource_table', array('dsid' => $datasource->getAttribute('id'), 'table' => $form['table-name'])));
	}

	protected function saveDatasources($dom) {
		$xml = $dom->saveXML(null, LIBXML_NOEMPTYTAG);
		$dom = new \DOMDocument();
		$dom->preserveWhiteSpace  = false;
		$dom->formatOutput = true;
		$dom->loadXml($xml);
		$formatted = preg_replace_callback('/^( +)</m', function($a) { 
			return str_repeat("\t", intval(strlen($a[1]) / 2)).'<'; 
		}, $dom->saveXML(null, LIBXML_NOEMPTYTAG));
		file_put_contents($this->db_dir."/DataSources.xml", $formatted);
	}

	protected function addTableRow($form, $table, $database) {
		if ($form['id'] > 0) {
			return $this->errorResponse($form, "This record already exists.");
		}
		if (($result = $this->addDBTableRow($form, $table, $database)) !== true) {
			return $this->errorResponse($form, $result);
		}
		$form['id'] = $database->lastInsertId($table);
		$response = new Response();
		$response->setContent(json_encode($form));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	protected function updateTableRow($form, $table, $database) {
		if ($form['id'] == 0) {
			return $this->addTableRow ($form, $table, $database);
		}
		if (($result = $this->updateDBTableRow($form, $table, $database)) !== true) {
			return $this->errorResponse($form, $result);
		}
		$response = new Response();
		$response->setContent(json_encode($form));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	protected function deleteTableRow($form, $table, $database) {
		if ($form['id'] == 0) {
			return $this->errorResponse($form, "There's no record with id 0.");
		}
		if (($result = $this->deleteDBTableRow($form, $table, $database)) !== true) {
			return $this->errorResponse($form, $result);
		}
		$response = new Response();
		$response->setContent(json_encode($form));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	protected function doEditTable($form, $table, $database) {
		if (($result = $this->editDBTable($form, $table, $database)) !== true) {
			return $this->errorResponse($form, $result);
		}
		$dom = dom_import_simplexml($this->datasources)->ownerDocument;
		$xpath = new \DOMXPath($dom);
		$datasource = $xpath->query("/DataSources/DataSource[@type='internal' and @database='".$database->getId()."']")->item(0);
		$tables = $datasource->getElementsByTagName('Table');
		$len = $tables->length;
		for($i = 0; $i < $len; $i++) {
			$name = $tables->item($i)->getAttribute('name');
			if ($name == $table) {
				$theTable = $tables->item($i);
				$theTable->setAttribute('name', $form['table-name']);
				$theTable->setAttribute('label', $form['table-label']);
				$descr = $dom->createElement("Description");
				$descr->appendChild($dom->createCDATASection(preg_replace("/(\<br\>)+$/", "", $form['table-description'])));
				$oldDescr = $theTable->getElementsByTagName('Description');
				if ($oldDescr->length > 0) {
					$theTable->replaceChild ($descr, $oldDescr->item(0));
				} else {
					$children = $theTable->getElementsByTagName('*');
					if ($children->length > 0) {
						$theTable->insertBefore($descr, $children->item(0));
					} else {
						$theTable->appendChild($descr);
					}
				}
				$columns = $theTable->getElementsByTagName('Column');
				foreach ($columns as $column) {
					$theTable->removeChild($column);
				}
				foreach ($form['field'] as $i => $field) {
					$column = $dom->createElement("Column");
					$column->setAttribute('id', $i + 1);
					$column->setAttribute('name', $field);
					$column->setAttribute('type', $form['type'][$i]);
					$column->setAttribute('label', $form['label'][$i]);
					$descr = $dom->createElement("Description");
					$descr->appendChild($dom->createCDATASection(preg_replace("/(\<br\>)+$/", "", $form['description'][$i])));
					$column->appendChild($descr);
					if ($form['type'][$i] == 'choice' || $form['type'][$i] == 'multichoice') {
						$choices = $dom->createElement("Choices");
						if (isset($form['field-'.$i.'-choicesource-datasource'])) {
							$source = $dom->createElement("Source");
							$source->setAttribute('id', 1);
							$source->setAttribute('datasource', $form['field-'.$i.'-choicesource-datasource']);
							$source->setAttribute('returnType', $form['field-'.$i.'-choicesource-returnType']);
							$source->setAttribute('valueColumn', $form['field-'.$i.'-choicesource-valueColumn']);
							$source->setAttribute('labelColumn', $form['field-'.$i.'-choicesource-labelColumn']);
							if (($form['field-'.$i.'-choicesource-datasource'] == 'internal' || $form['field-'.$i.'-choicesource-datasource'] == 'database')) {
								$source->setAttribute('request', $form['field-'.$i.'-choicesource-request']);
							} else {
								if (isset($form['field-'.$i.'-choicesource-returnPath'])) {
									$source->setAttribute('returnPath', $form['field-'.$i.'-choicesource-returnPath']);
								}
								if ($form['field-'.$i.'-choicesource-returnType'] == 'csv') {
									if (isset($form['field-'.$i.'-choicesource-separator'])) {
										$source->setAttribute('separator', $form['field-'.$i.'-choicesource-separator']);
									}
									if (isset($form['field-'.$i.'-choicesource-delimiter'])) {
										$source->setAttribute('delimiter', $form['field-'.$i.'-choicesource-delimiter']);
									}
								}
							}
							$choices->appendChild($source);
						} else{
							foreach ($form['field-'.$i.'-choice-value'] as $c => $value) {
								$choice = $dom->createElement("Choice");
								$choice->setAttribute('id', $c + 1);
								$choice->setAttribute('value', $value);
								$choice->setAttribute('label', $form['field-'.$i.'-choice-label'][$c]);
								$choices->appendChild($choice);
							}
						}
						$column->appendChild($choices);
					}
					$theTable->appendChild($column);
				}
				break;
			}
		}
		$this->saveDatasources($dom);
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasource_table', array('dsid' => $datasource->getAttribute('id'), 'table' => $table)));
	}

	protected function dropTable($table, $database) {
		if (($result = $this->dropDBTable($table, $database)) !== true) {
			return $this->errorResponse($form, $result);
		}
		$dom = dom_import_simplexml($this->datasources)->ownerDocument;
		$xpath = new \DOMXPath($dom);
		$datasource = $xpath->query("/DataSources/DataSource[@type='internal' and @database='".$database->getId()."']")->item(0);
		$tables = $datasource->getElementsByTagName('Table');
		$len = $tables->length;
		$maxId = 0;
		for($i = 0; $i < $len; $i++) {
			$name = $tables->item($i)->getAttribute('name');
			if ($name == $table) {
				$datasource->removeChild($tables->item($i));
				break;
			}
		}
		$this->saveDatasources($dom);
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasource', array('dsid' => $datasource->getAttribute('id'))));
	}

	protected function doEditDatasource($dsid, $form) {
		$dom = dom_import_simplexml($this->datasources)->ownerDocument;
		$xpath = new \DOMXPath($dom);
		$datasource = $xpath->query("/DataSources/DataSource[@id='".$dsid."']")->item(0);
		$oldType = $datasource->getAttribute('type');
		$type = $form['datasource-type'];
		$datasource->setAttribute('type', $type);
		$datasource->setAttribute('name', $form['datasource-name']);
		$descr = $dom->createElement("Description");
		$descr->appendChild($dom->createCDATASection(preg_replace("/(\<br\>)+$/", "", $form['datasource-description'])));
		$oldDescr = $datasource->getElementsByTagName('Description');
		if ($oldDescr->length > 0) {
			$datasource->replaceChild ($descr, $oldDescr->item(0));
		} else {
			$children = $datasource->getElementsByTagName('*');
			if ($children->length > 0) {
				$datasource->insertBefore($descr, $children->item(0));
			} else {
				$datasource->appendChild($descr);
			}
		}
		$sameDatabase = true;
		if ($type == 'internal' && $oldType == 'internal') {
			$database = $xpath->query("/DataSources/Databases/Database[@id='".$datasource->getAttribute('database')."']")->item(0);
			if ($database->getAttribute('type') != $form['datasource-database-type']) {
				$sameDatabase = false;
			} else if ($database->getAttribute('name') != $form['datasource-database-name']) {
				$sameDatabase = false;
			} else if ($database->getAttribute('type') == 'mysqli' || $database->getAttribute('type') == 'pgsql') {
				if ($database->getAttribute('host') != $form['datasource-database-host']) {
					$sameDatabase = false;
				} else if ($database->getAttribute('port') != $form['datasource-database-port']) {
					$sameDatabase = false;
				} else if ($database->getAttribute('user') != $form['datasource-database-user']) {
					$sameDatabase = false;
				}
			}
			if (! $sameDatabase) {
				$fromDatabase = $this->getDatabase($dsid);
			}
		}
		switch($type) {
			case 'internal':
			case 'database':
				$dbtype = $form['datasource-database-type'];
				if ($oldType == 'uri') {
					$datasource->removeAttribute ('uri');
					$datasource->removeAttribute ('method');
					$dbs = $xpath->query("/DataSources/Databases");
					$db = $dbs->item(0)->getElementsByTagName('Database');
					$len = $db->length;
					$maxId = 0;
					for($i = 0; $i < $len; $i++) {
						$id = (int)$db->item($i)->getAttribute('id');
						if ($id > $maxId) {
							$maxId = $id;
						}
					}
					$database = $dom->createElement("Database");
					$database->setAttribute('id', $maxId + 1);
					$database->setAttribute('type', $dbtype);
					$database->setAttribute('name', $form['datasource-database-name']);
					$database->setAttribute('label', $form['datasource-database-label']);
					if ($dbtype == 'mysqli' || $dbtype == 'pgsql') {
						$database->setAttribute('host', $form['datasource-database-host']);
						$database->setAttribute('port', $form['datasource-database-port']);
						$database->setAttribute('user', $form['datasource-database-user']);
						if (isset($form['datasource-database-password'])) {
							$database->setAttribute('password', $form['datasource-database-password']);
						}
					}
					$dbs->item(0)->appendChild($database);
					$datasource->setAttribute('database', $database->getAttribute('id'));
				} else {
					$database = $xpath->query("/DataSources/Databases/Database[@id='".$datasource->getAttribute('database')."']")->item(0);
					$oldDbtype = $database->getAttribute('type');
					$database->setAttribute('type', $dbtype);
					$database->setAttribute('name', $form['datasource-database-name']);
					$database->setAttribute('label', $form['datasource-database-label']);
					if ($dbtype == 'mysqli' || $dbtype == 'pgsql') {
						$database->setAttribute('host', $form['datasource-database-host']);
						$database->setAttribute('port', $form['datasource-database-port']);
						$database->setAttribute('user', $form['datasource-database-user']);
						if (isset($form['datasource-database-password'])) {
							$database->setAttribute('password', $form['datasource-database-password']);
						} elseif ($database->hasAttribute('password')) {
							$database->removeAttribute ('password');
						}
					} else {
						if ($oldDbtype == 'mysqli' || $oldDbtype == 'pgsql') {
							$database->removeAttribute ('host');
							$database->removeAttribute ('port');
							$database->removeAttribute ('user');
							if ($database->hasAttribute('password')) {
								$database->removeAttribute ('password');
							}
						}
					}
				}
				break;
			case 'uri':
				$datasource->setAttribute('uri', $form['datasource-name']);
				$datasource->setAttribute('method', $form['datasource-method']);
				if ($oldType != 'uri') {
					$databases = $xpath->query("/DataSources/Databases")->item(0);
					$database = $xpath->query("/DataSources/Databases/Database[@id='".$datasource->getAttribute('database')."']")->item(0);
					$datasource->removeAttribute ('database');
					$databases->removeChild($database);
				}
				break;
		}
		$this->saveDatasources($dom);
		if ($type == 'internal' && $oldType == 'internal' && ! $sameDatabase) {
			$this->datasources = simplexml_import_dom($dom);
			$this->migrateDB($dsid, $dbtype, $fromDatabase);
		}
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasource', array('dsid' => $datasource->getAttribute('id'))));
	}

	protected function dropDatasource ($dsid) {
		$dom = dom_import_simplexml($this->datasources)->ownerDocument;
		$xpath = new \DOMXPath($dom);
		$datasource = $xpath->query("/DataSources/DataSource[@id='".$dsid."']")->item(0);
		$type = $datasource->getAttribute('type');
		$descr = $datasource->getElementsByTagName('Description');
		if ($type == 'internal' || $type == 'database') {
			$dbs = $xpath->query("/DataSources/Databases");
			$database = $xpath->query("/DataSources/Databases/Database[@id='".$datasource->getAttribute('database')."']")->item(0);
			$dbtype = $database->getAttribute('type');
			if ($type == 'internal' && ($dbtype == 'jsonsql' || $dbtype == 'sqlite')) {
				$dbname = $database->getAttribute('name');
				// TODO : faut-il effacer les fichiers bases de données ?
			}
			$dbs->item(0)->removeChild($database);
		}
		$dss = $xpath->query("/DataSources");
		$dss->item(0)->removeChild ($datasource);
		$this->saveDatasources($dom);
		return new RedirectResponse($this->generateUrl('eureka_g6k_admin_datasources'));
	}

}
