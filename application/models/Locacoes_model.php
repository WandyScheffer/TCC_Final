<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Locacoes_model extends CI_Model {

    
    public function __construct()
    {
        parent::__construct();
        
    }
    
    public function realizaLocacao($array_insercao = null)
    {
        // $array_insercao['dt_devolucao_prevista'] = " date 'now()' + integer '7' ";
        // $this->db->insert('"ALUNO4M21".mltb_locacao', $array_insercao);

        $sql = 'insert into 
                "ALUNO4M21".mltb_locacao 
                (dt_devolucao_prevista, id_locador, id_bibliotecario, id_exemplar) 
                values ('. "date 'now()' + integer '7'" .', ? , ? , ? );';
        $query = $this->db->query($sql, array($array_insercao['id_locador'], $array_insercao['id_bibliotecario'], $array_insercao['id_exemplar']));
        // $query->result();
        $locar['est_locado'] = true;

        $this->db->where('id_exemplar', $array_insercao['id_exemplar']);
        $this->db->update('"ALUNO4M21".mltb_exemplar_livro ', $locar);
        
    }

    public function verificaExemplar($id_exemplar = null)
    {
        
        $this->db->where('id_exemplar =', $id_exemplar);
        $this->db->where('est_locado = ', 'true');
        
        return $this->db->get('"ALUNO4M21".mltb_exemplar_livro', null, null)->result_array();
        
    }

    public function verificaUser($id_user = null)
    {
        $this->db->where('id_pessoa = ', $id_user);
        $this->db->where('permissao', 3);
        
        return $this->db->get('"ALUNO4M21".mltb_pessoa', null, null)->result_array();
        
    }

    public function pesquisaEncerra($id_user = null)
    {
        $id['id_user'] = $id_user;
        $sql = 'select li.nm_titulo, 
                cast (to_char((now() - lo.dt_devolucao_prevista),'."'dd'".') as integer)*(select vl_multa from "ALUNO4M21".mltb_valormulta order by id_valor desc limit 1) as multa
                from "ALUNO4M21".mltb_locacao as lo,
                "ALUNO4M21".mltb_exemplar_livro as e,
                "ALUNO4M21".mltb_livro as li 
                where lo.id_locador = ? and
                lo.id_exemplar = e.id_exemplar and
                e.id_livro = li.id_livro and
                dt_devolucao is null';
        $query = $this->db->query($sql, array($id))->result_array();
        return $query;
    }

    public function encerrar($array_update = null)
    {
        $array_update['dt_devolucao'] = 'now()';

        $this->db->where('id_locador = ', $array_update['id_locador']);
        $this->db->where('dt_devolucao', null);
        
        
        $this->db->update('"ALUNO4M21".mltb_locacao', $array_update);
        
    }

    public function identificaExemplarEncerra($id_user = null)
    {
        $this->db->select('id_exemplar');
        $this->db->where('id_locador', $id_user);
        $this->db->where('dt_devolucao', null);
        
        return $this->db->get('"ALUNO4M21".mltb_locacao', null, null)->result_array();
        
        
    }

    public function livroDeVoltaEncerra($id_exemplar = null)
    {
        $this->db->where('id_exemplar', $id_exemplar);
        $array_update['est_locado'] = false;
        $this->db->update('"ALUNO4M21".mltb_exemplar_livro ', $array_update);
        
    }

    public function renovamento($id_user = null)
    {
        $sql = 'update "ALUNO4M21".mltb_locacao set dt_devolucao_prevista = (date '."'now()'".' + integer '."'7'".') where id_locador = 6 and dt_devolucao is null';
        $query = $this->db->query($sql, array($id_user));
    }

    public function listaLocacoes($limit, $offset)
    {
        $sql = 'select pe.id_pessoa, pe.nm_pessoa as locador, 
                li.nm_titulo as titulo, lo.id_exemplar, lo.vl_pago,
                cast (to_char((now() - lo.dt_devolucao_prevista),'."'dd'".') as integer)*(select vl_multa 
                																	from "ALUNO4M21".mltb_valormulta 
                																	order by id_valor desc limit 1) as multa,
                to_char (lo.dt_locacao, '."'dd/mm/yyyy'".') as dt_locacao,
                to_char (lo.dt_devolucao_prevista, '."'dd/mm/yyyy'".') as dt_devolucao_prevista,
                to_char (lo.dt_devolucao, '."'dd/mm/yyyy'".') as dt_devolucao,
                pb.nm_pessoa as biblio_locar
                from "ALUNO4M21".mltb_locacao as lo,
                "ALUNO4M21".mltb_pessoa as pe,
                "ALUNO4M21".mltb_livro as li,
                "ALUNO4M21".mltb_exemplar_livro as e,
                "ALUNO4M21".mltb_pessoa as pb
                where lo.id_locador = pe.id_pessoa and
                lo.id_exemplar = e.id_exemplar and
                e.id_livro = li.id_livro and
                lo.id_bibliotecario = pb.id_pessoa
                limit ? offset ?';
        $query = $this->db->query($sql, array($limit, $offset))->result_array();
        return $query;

    }

}


