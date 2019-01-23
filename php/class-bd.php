<?php
class BD {
	private $con;
	private $conectado = false;
	private $setBD = false;
	function __construct($host, $usuario, $senha, $porta = 3306)
	{
		try {
			$this->con = new PDO("mysql:host=".$host.";port=".$porta.";", $usuario, $senha, array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
			$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conectado = true;
		} catch(PDOException $e) {
			echo "Erro: ".$e->getMessage();
		}
	}
	public function getConexao()
	{
		if ( $this->conectado ) {
			return array("status"=>true, "msg"=>"OK", "res"=>$this->con);
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function criarBD($bd)
	{
		if ( $this->conectado ) {
			try {
				$this->con->beginTransaction();
				$stmt = $this->con->prepare("CREATE DATABASE ".$bd." CHARACTER SET utf8 COLLATE utf8_general_ci;");
				$stmt->execute();
				$this->con->commit();
				return array("status"=>true, "msg"=>"OK");
			} catch(PDOException $e) {
				$this->con->rollBack();
				return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function criarTabela($query)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare($query);
					$stmt->execute();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK");
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function setBD($bd)
	{
		if ( $this->conectado ) {
			try {
				$this->con->beginTransaction();
				$stmt = $this->con->prepare("USE ".$bd.";");
				$stmt->execute();
				$this->con->commit();
				$this->setBD = true;
				return array("status"=>true, "msg"=>"OK", "res"=>"");
			} catch(PDOException $e) {
				$this->con->rollBack();
				return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function getDescTabela($tabela)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("DESCRIBE ".$tabela.";");
					$stmt->execute();
					$res = $stmt->fetchAll();
					$this->con->commit();
					$dados;
					for ($k=0; $k<count($res); $k++) {
						foreach($res[$k] as $chave=>$valor) {
							if (!is_numeric($chave)) {
								if (!empty($valor)) {
									$dados[$k] .= $chave.": ".$valor." | ";
								}
							}
						}
						$t1 = strlen($dados[$k]);
						$dados[$k] = substr($dados[$k], 0, $t1-3);
					}
					return array("status"=>true, "msg"=>"OK", "res"=>$dados);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function getTabelas()
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$tabelas;
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("SHOW TABLES;");
					$stmt->execute();
					$res = $stmt->fetchAll();
					$this->con->commit();
					for ($k=0; $k<count($res); $k++) {
						$tabelas[$k] = $res[$k][0];
					}
					return array("status"=>true, "msg"=>"OK", "res"=>$tabelas);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function getColunas($tabela)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$colunas;
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("SHOW COLUMNS FROM ".$tabela.";");
					$stmt->execute();
					$res = $stmt->fetchAll();
					$this->con->commit();
					for ($k=0; $k<count($res); $k++) {
						$colunas[$k] = $res[$k][0];
					}
					return array("status"=>true, "msg"=>"OK", "res"=>$colunas);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function insert($tabela, $valores)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$c = "";
				$v = "";
				foreach($valores as $chave=>$valor) {
					$c .= $chave.", ";
					$v .= "'".$valor."', ";
				}
				$t0 = strlen($c);
				$c = substr($c, 0, $t0-2);
				$t1 = strlen($v);
				$v = substr($v, 0, $t1-2);
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("INSERT INTO ".$tabela." (".$c.") VALUES (".$v.");");
					$stmt->execute();
					$res = $stmt->rowCount();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK", "res"=>$res);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function select($tabela, $v = 0, $c = 0)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$valores = "";
				$condicao = " WHERE ";
				if (is_array($v)) {
					foreach($v as $valor) {
						$valores .= $valor.", ";
					}
					$t0 = strlen($valores);
					$valores = substr($valores, 0, $t0-2);
				} else {
					$valores = "*";
				}
				if (is_array($c)) {
					foreach($c as $chave=>$valor) {
						$condicao .= $chave." = '".$valor."' AND ";
					}
					$t1 = strlen($condicao);
					$condicao = substr($condicao, 0, $t1-5);
				} else {
					$condicao = "";
				}
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("SELECT ".$valores." FROM ".$tabela.$condicao.";");
					$stmt->execute();
					$res = $stmt->fetchAll();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK", "res"=>$res);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function update($tabela, $v = 0, $c = 0)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$valores = "";
				$condicao = " WHERE ";
				foreach($v as $chave=>$valor) {
					$valores .= $chave." = '".$valor."', ";
				}
				$t0 = strlen($valores);
				$valores = substr($valores, 0, $t0-2);
				foreach($c as $chave=>$valor) {
					$condicao .= $chave." = '".$valor."' AND ";
				}
				$t1 = strlen($condicao);
				$condicao = substr($condicao, 0, $t1-5);
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("UPDATE ".$tabela." SET ".$valores.$condicao.";");
					$stmt->execute();
					$res = $stmt->rowCount();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK", "res"=>$res);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function delete($tabela, $c = 0)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$condicao = " WHERE ";
				if (is_array($c)) {
					foreach($c as $chave=>$valor) {
						$condicao .= $chave." = '".$valor."' AND ";
					}
					$t1 = strlen($condicao);
					$condicao = substr($condicao, 0, $t1-5);
				} else {
					$condicao = "";
				}
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("DELETE FROM ".$tabela.$condicao.";");
					$stmt->execute();
					$res = $stmt->rowCount();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK", "res"=>$res);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function selectLike($tabela, $busca, $valores = 0)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$b = "";
				$v = "";
				if (is_array($busca)) {
					foreach($busca as $coluna=>$valor) {
						$b .= $coluna." LIKE '%".$valor."%'";
					}
				} else {
					return array("status"=>false, "msg"=>"Erro: não há condição de busca!", "res"=>"");
				}
				if (is_array($valores)) {
					foreach($valores as $valor) {
						$v .= $valor.", ";
					}
					$t1 = strlen($v);
					$v = substr($v, 0, $t1-2);
				} else {
					$v = "*";
				}
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("SELECT ".$v." FROM ".$tabela." WHERE ".$b.";");
					$stmt->execute();
					$res = $stmt->fetchAll();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK", "res"=>$res);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function innerJoin($tabelas, $condicoes, $valores = 0)
	{
		if ( $this->conectado ) {
			if ( $this->setBD ) {
				$v = "";
				$tc = "";
				if (is_array($valores)) {
					foreach ($valores as $valor) {
						$v .= $valor.", ";
					}
					$t1 = strlen($v);
					$v = substr($v, 0, $t1-2);
				} else {
					$v = "*";
				}
				for ($k=0; $k<count($tabelas); $k++) {
					if ($k == 0) {
						if (($k+1)<=count($tabelas)) {
							$tc .= $tabelas[$k]." INNER JOIN ".$tabelas[$k+1]." ON (".$condicoes[$k].")";
						}
					} else {
						if (($k+1)<count($tabelas)) {
							$tc .= " INNER JOIN ".$tabelas[$k+1]." ON (".$condicoes[$k].")";
						}
					}
				}
				try {
					$this->con->beginTransaction();
					$stmt = $this->con->prepare("SELECT ".$v." FROM ".$tc.";");
					$stmt->execute();
					$stmt->fetchAll();
					$this->con->commit();
					return array("status"=>true, "msg"=>"OK", "res"=>$res);
				} catch(PDOException $e) {
					$this->con->rollBack();
					return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
				}
			} else {
				return array("status"=>false, "msg"=>"Erro: banco de dados não selecionado!", "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
	public function query($query, $valores)
	{
		if ( $this->conectado ) {
			try {
				$res;
				$this->con->beginTransaction();
				$stmt = $this->con->prepare($query);
				if (is_array($valores)) {
					$stmt->execute($valores);
				} else {
					$stmt->execute();
				}
				if ( (preg_match("/select/", strtolower($query))) || (preg_match("/show/", strtolower($query))) ) {
					$res = $stmt->fetchAll();
				} elseif ( (preg_match("/insert/", strtolower($query))) || (preg_match("/update/", strtolower($query))) || (preg_match("/delete/", strtolower($query))) ) {
					$res = $stmt->rowCount();
				} else {
					$res = "";
				}
				$this->con->commit();
				return array("status"=>true, "msg"=>"OK", "res"=>$res);
			} catch(PDOException $e) {
				$this->con->rollBack();
				return array("status"=>false, "msg"=>"Erro: ".$e->getMessage(), "res"=>"");
			}
		} else {
			return array("status"=>false, "msg"=>"Erro: não há conexão!", "res"=>"");
		}
	}
}
?>
