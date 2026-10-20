<?php
foreach (["pgsql","pgsql2","pgsql3","pgsql4","pgsql5","pgsql6","pgsql7","pgsql8"] as $cn) {
 try { $c=DB::connection($cn); $db=$c->selectOne("select current_database() d")->d;
  $cols=$c->select("select table_name,column_name from information_schema.columns where table_schema='public' and data_type in ('character varying','text') and (column_name ~* 'name|descr|remark|title|note|product|item')");
  $n=0;
  foreach($cols as $x){ try{ $r=$c->select("select count(*) n from \"{$x->table_name}\" where \"{$x->column_name}\" ~* ?",["ye live|live in indo|cat(egory)? ?3 .*ye|tiket.*ye"]); if($r[0]->n>0) echo "$cn/$db {$x->table_name}.{$x->column_name}: {$r[0]->n}\n"; }catch(Throwable $e){} }
  echo "done $cn $db (".count($cols)." cols)\n";
 } catch(Throwable $e){ echo "$cn ERR\n"; }
}
