<?php

namespace App\Http\Controllers;

use App\Imports\ImportaIccid;
use App\Models\CompanyModel;
use App\Models\IccidModel;
use App\Models\TokenSisModel;
use App\Models\UsersModel;
use App\Models\vwparceirosmodel;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CompanyController extends Controller
{
    public function CadastraCompany(Request $request)
    {
        if ($this->checatoken($request->token) == true) {
            $request->validate([
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $company = new CompanyModel();
            $user = new UsersModel();
            $token = new TokenSisModel();

            $reqmatriz = $request->matriz;

            if ($reqmatriz == true) {
                $cpfUserLogGet = $user::where('cpf', $request->cpfUserLog)->first();
                $company->useradminid = $cpfUserLogGet->useradminid;

                if ($request->hasFile('logo')) {
                    $path = $request->file('logo')->store('logos', 'public');
                    $company->logotipo = $path;
                } else {
                    $company->logotipo = null;
                }

                $company->companyname = $request->companyname;
                $company->cnpj = $request->cnpj;
                $company->tradename = $request->tradename;
                $company->type = 'Matriz';
                $company->matrizname = $request->matrizname;
                $company->cep = $request->cep;
                $company->rua = $request->rua;
                $company->cidade = $request->cidade;
                $company->uf = $request->uf;
                $company->pais = $request->pais;
                $company->complemento = $request->complemento;
                $company->numero = $request->numero;
                $company->celular = $request->celular;
                $company->whatsapp = $request->whatsapp;
                $company->latitude = $request->latitude;
                $company->longitude = $request->longitude;
                $company->matrizid = $company::max('matrizid') + 1;
                $company->save();
                $companyid = $company->companyid;
            } else {
                $cpfUserLogGet = $user::where('cpf', $request->cpfUserLog)->first();
                $company->useradminid = $cpfUserLogGet->useradminid;

                if ($request->hasFile('logo')) {
                    $path = $request->file('logo')->store('logos', 'public');
                    $company->logotipo = $path;
                } else {
                    $company->logotipo = null;
                }

                $company->companyname = $request->companyname;
                $company->cnpj = $request->cnpj;
                $company->tradename = $request->tradename;
                $company->type = 'Filial';
                $company->matrizname = null;
                $company->cep = $request->cep;
                $company->rua = $request->rua;
                $company->cidade = $request->cidade;
                $company->uf = $request->uf;
                $company->pais = $request->pais;
                $company->complemento = $request->complemento;
                $company->numero = $request->numero;
                $company->celular = $request->celular;
                $company->whatsapp = $request->whatsapp;
                $company->latitude = $request->latitude;
                $company->longitude = $request->longitude;
                $company->parentid = $request->matrizid;
                $company->save();
                $companyid = $company->companyid;
            }

            $guarda = $token::where('useradminid', $cpfUserLogGet->useradminid)->first();
            $better_token = bin2hex(random_bytes(25));

            if (empty($guarda)) {
                $token->parceiro = $cpfUserLogGet->name;
                $token->useradminid = $cpfUserLogGet->useradminid;
                $token->token = $better_token;
                $token->companyid = $companyid;
                $token->save();
            } else {
                $token->parceiro = $cpfUserLogGet->name;
                $token->useradminid = $cpfUserLogGet->useradminid;
                $token->token = $better_token;
                $token->companyid = $companyid;
                $token->save();
            }

            return $company;
        } else {
            return response('O token não foi autorizado', 401);
        }
    }

    public function EditaCompany(Request $request)
    {
        if ($this->checatoken($request->token) == true) {
            $company = new CompanyModel();

            $permite = false;
            $tk = new TokenSisModel();

            $empresa = $tk::where('token', '=', $request->token)->first();

            if ($empresa->parceiro == 'INFINITI' || $empresa->parceiro == 'PLAY MÓVEL') {
                $permite = true;
            }

            $att = $company::where('companyid', '=', $request->companyid)->first();
            $att1 = $company::where('companyid', '=', $request->companyid)->first();
            $att->companyname = $request->companyname;
            $att->cnpj = $request->cnpj;
            $att->tradename = $request->tradename;
            $att->parentcompanyid = 1;
            $att->nomeparceiro = $request->nomeparceiro;
            $att->email = $request->email;
            $att->celular = $request->celular;
            $att->telefone = $request->telefone;
            $att->cep = $request->cep;
            $att->endereco = $request->endereco;
            $att->numeroendereco = $request->numeroendereco;
            $att->complemento = $request->complemento;
            $att->bairro = $request->bairro;
            $att->inscricaomunicipal = $request->inscricaomunicipal;
            $att->inscricaoestadual = $request->inscricaoestadual;
            $att->atualizadoem = Carbon::now();
            $att->observacoes = $request->observacoes;
            $att->walletid = $request->walletid;
            $att->link_playstore = $request->link_playstore;
            $att->link_appstore = $request->link_appstore;
            $att->link_website = $request->link_website;
            $att->link_chat = $request->link_chat;
            $att->link_contrato = $request->link_contrato;
            $att->consultor = $request->consultor;
            if ($permite == true) {
                if (isset($request->mvnoparent)) {
                    $att->mvnoparent = $request->mvnoparent; // Campo que vai indicar se a empresa que está entrando é uma empresa MVNO
                }
            }
            if (isset($request->asaastoken)) {
                $att->asaastoken = $request->asaastoken; // Campo que vai indicar se a empresa que está entrando é uma empresa MVNO
            }
            if (isset($request->surflogin)) {
                $att->surflogin = $request->surflogin; // Campo que vai indicar se a empresa que está entrando é uma empresa MVNO
            }
            if (isset($request->surfpassword)) {
                $att->surfpassword = $request->surfpassword; // Campo que vai indicar se a empresa que está entrando é uma empresa MVNO
            }
            if (isset($request->mvnoparentid)) {
                if ($request->mvnoparentid <> '') {
                    $att->mvnoparentid  = $request->mvnoparentid;
                }
            }
            if (isset($request->appTheme)) {
                if ($request->appTheme <> '') {
                    $att->apptheme  = $request->appTheme;
                }
            }

            if (isset($request->appversion)) {
                if ($request->appversion <> '') {
                    $att->appversion  = $request->appversion;
                }
            }

            $att->save();
            if ($att->logotipo == null) {
                $logo = '';
            } else {
                $logo = stream_get_contents($att->logotipo);
            }

            $array = [
                'companyId' => $att->companyid,
                'companyname' => $att->companyname,
                'cnpj' => $att->cnpj,
                'tradename' => $att->tradename,
                'logotipo' => $logo,
                'nomeparceiro' => $att->nomeparceiro,
                'email' => $att->email,
                'celular' => $att->celular,
                'telefone' => $att->telefone,
                'cep' => $att->cep,
                'endereco' => $att->endereco,
                'numeroendereco' => $att->numeroendereco,
                'complemento' => $att->complemento,
                'bairro' => $att->bairro,
                'inscricaomunicipal' => $att->inscricaomunicipal,
                'inscricaoestadual' => $att->inscricaoestadual,
                'observacoes' => $att->observacoes,
                'walletid' => $att->walletid,
                'link_playstore' => $att->link_playstore,
                'link_appstore' => $att->link_appstore,
                'link_website' => $att->link_website,
                'link_chat' => $att->link_chat,
                'link_contrato' => $att->link_contrato,
                'consultor' => $att->consultor,
                'appTheme' => $att->apptheme,
                'appversion' => $att->appversion,
                'mvnoparent' => $att->mvnoparent,
                'mvnoparentid' => $att->mvnoparentid,
            ];


            $token = new TokenSisModel();

            $date = new DateTime('now');
            $date->modify('+6 month'); // or you can use '-90 day' for deduct
            $date = $date->format('d-m-Y');
            $better_token = bin2hex(random_bytes(25));

            $guarda = $token::where('parceiro', '=', $att1->companyname)->first();

            $criadopor = $att->consultor;
            if ($criadopor == '') {
                $criadopor = 'SISTEMA';
            }

            try {
                if (empty($guarda)) {
                    $token->parceiro = $att->companyname;
                    $token->token = $better_token;
                    $token->validade = $date;
                    $token->criado_por = $criadopor;
                    $token->save();
                    response(array('token' => $token->token, 'data_validade' => $token->validade, 'state' => 'CRIADO'), 201);
                } else {
                    $guarda->parceiro = $att->companyname;
                    $guarda->criado_por = $criadopor;
                    $guarda->validade = $date;
                    $guarda->save();
                    response(array('token' => $guarda->token, 'data_validade' => $guarda->validade, 'state' => 'Atualizado'), 201);
                }
            } catch (exception $e) {
            }
            return $array;
        } else {
            return response('O token não foi autorizado', 401);
        }
    }

    public function inserelogotipo(Request $request)
    {
        if ($this->checatoken($request->token) == true) {
            $company = new CompanyModel();

            $path = $request->file('logo')->getRealPath();
            $logo = file_get_contents($path);
            $base64 = base64_encode($logo);

            $att = $company::where('companyid', '=', $request->companyid)->first();
            $att->logotipo = $base64;
            $att->timestamps = false;
            $att->save();
            return $att;
        } else {
            return response('O token não foi autorizado', 401);
        }
    }

    public function consultaToken(Request $request)
    {
        if ($request->jwt === 'erGGBCeLwOVPsfO7ArSOSw') {
            $token = new TokenSisModel();
            $tk = $token::where('parceiro', '=', $request->parceiro)->first('token');
            return $tk->token;
        } else {
            return response('Jwt não encontrado', 590);
        }
    }

    public function consultaEmpresa(Request $request)
    {
        if ($this->checatoken($request->token) == true) {
            $company = new CompanyModel();
            $tokensis = new TokenSisModel();

            $att = $company::where('companyid', '=', $request->companyid)->first();

            if (empty($att)) {
                return Response('Não foi encontrado pelo CompanyId', 404);
            }

            $tok = $tokensis::where('parceiro', '=', $att->tradename)->first();


            if (isset($tok)) {
                if ($tok->token == '') {
                    return response('Não foi encontrado token , Favor entrar em contato com o administrador para ser gerado', 404);
                } else {
                    $toknm = $tok->token;
                }
            } else {
                return response('Não foi encontrado token , Favor entrar em contato com o administrador para ser gerado', 404);
            }

            if ($att->logotipo == null) {
                $logo = '';
            } else {
                $logo = stream_get_contents($att->logotipo);
            }
            $array = [
                'companyId' => $att->companyid,
                'companyname' => $att->companyname,
                'cnpj' => $att->cnpj,
                'tradename' => $att->tradename,
                'logotipo' => $logo,
                'nomeparceiro' => $att->nomeparceiro,
                'email' => $att->email,
                'celular' => $att->celular,
                'telefone' => $att->telefone,
                'cep' => $att->cep,
                'endereco' => $att->endereco,
                'numeroendereco' => $att->numeroendereco,
                'complemento' => $att->complemento,
                'bairro' => $att->bairro,
                'inscricaomunicipal' => $att->inscricaomunicipal,
                'inscricaoestadual' => $att->inscricaoestadual,
                'observacoes' => $att->observacoes,
                'walletid' => $att->walletid,
                'link_playstore' => $att->link_playstore,
                'link_appstore' => $att->link_appstore,
                'link_website' => $att->link_website,
                'link_chat' => $att->link_chat,
                'pospago' => $att->pospagoativa,
                'link_contrato' => $att->link_contrato,
                'consultor' => $att->consultor,
                'token' => $toknm,
                'appTheme' => $att->apptheme,
                'appversion' => $att->appversion,
                'mvnoparent' => $att->mvnoparent,
                'mvnoparentid' => $att->mvnoparentid,

            ];

            return $array;
        } else {
            return response('O token não foi autorizado', 401);
        }
    }

    public function consultatodos(Request $request)
    {
        if ($this->checatoken($request->token) == true) {
            $tk = new TokenSisModel();
            $company = new CompanyModel();


            $toke = $tk::where('token', '=', $request->token)->first();
            if ($toke->parceiro == 'INFINITI') {

                foreach ($company::orderby('companyid')->get() as $com) {
                    if ($com->logotipo == null) {
                        $logo = '';
                    } else {
                        $logo = stream_get_contents($com->logotipo);
                    }
                    $array[] = [
                        'companyId' => $com->companyid,
                        'companyname' => $com->companyname,
                        'cnpj' => $com->cnpj,
                        'tradename' => $com->tradename,
                        'logotipo' => $logo,
                        'nomeparceiro' => $com->nomeparceiro,
                        'email' => $com->email,
                        'celular' => $com->celular,
                        'telefone' => $com->telefone,
                        'cep' => $com->cep,
                        'endereco' => $com->endereco,
                        'numeroendereco' => $com->numeroendereco,
                        'complemento' => $com->complemento,
                        'bairro' => $com->bairro,
                        'inscricaomunicipal' => $com->inscricaomunicipal,
                        'inscricaoestadual' => $com->inscricaoestadual,
                        'observacoes' => $com->observacoes,
                        'link_playstore' => $com->link_playstore,
                        'link_appstore' => $com->link_appstore,
                        'link_website' => $com->link_website,
                        'link_chat' => $com->link_chat,
                        'link_contrato' => $com->link_contrato,
                        'consultor' => $com->consultor,
                        'appTheme' => $com->apptheme,
                        'appversion' => $com->appversion,
                        'mvnoparent' => $com->mvnoparent,
                        'mvnoparentid' => $com->mvnoparentid,



                    ];
                }
                return $array;
            } else {
                $empresa = $company::where('companyname', '=', $toke->parceiro)->first();
                if (isset($empresa->mvnoparent)) {
                    if ($empresa->mvnoparent == true) {

                        $data = $company::where('mvnoparentid', '=', $empresa->companyid)->orwhere('companyid', '=', $empresa->companyid)->get();
                        foreach ($data as $com) {
                            if ($com->logotipo == null) {
                                $logo = '';
                            } else {
                                $logo = stream_get_contents($com->logotipo);
                            }
                            $array[] = [
                                'companyId' => $com->companyid,
                                'companyname' => $com->companyname,
                                'cnpj' => $com->cnpj,
                                'tradename' => $com->tradename,
                                'logotipo' => $logo,
                                'nomeparceiro' => $com->nomeparceiro,
                                'email' => $com->email,
                                'celular' => $com->celular,
                                'telefone' => $com->telefone,
                                'cep' => $com->cep,
                                'endereco' => $com->endereco,
                                'numeroendereco' => $com->numeroendereco,
                                'complemento' => $com->complemento,
                                'bairro' => $com->bairro,
                                'inscricaomunicipal' => $com->inscricaomunicipal,
                                'inscricaoestadual' => $com->inscricaoestadual,
                                'observacoes' => $com->observacoes,
                                'link_playstore' => $com->link_playstore,
                                'link_appstore' => $com->link_appstore,
                                'link_website' => $com->link_website,
                                'link_chat' => $com->link_chat,
                                'link_contrato' => $com->link_contrato,
                                'consultor' => $com->consultor,
                                'appTheme' => $com->apptheme,
                                'appversion' => $com->appversion,
                                'mvnoparent' => $com->mvnoparent,
                                'mvnoparentid' => $com->mvnoparentid,


                            ];
                        }
                        if (isset($array)) {
                            return $array;
                        } else
                            return array(

                                'companyId' => '',
                                'companyname' => '',
                                'cnpj' => '',
                                'tradename' => '',
                                'logotipo' => '',
                                'nomeparceiro' => '',
                                'email' => '',
                                'celular' => '',
                                'telefone' => '',
                                'cep' => '',
                                'endereco' => '',
                                'numeroendereco' => '',
                                'complemento' => '',
                                'bairro' => '',
                                'inscricaomunicipal' => '',
                                'inscricaoestadual' => '',
                                'observacoes' => '',
                                'link_playstore' => '',
                                'link_appstore' => '',
                                'link_website' => '',
                                'link_chat' => '',
                                'link_contrato' => '',
                                'consultor' => '',
                                'appTheme' => '',
                                'appversion' => '',
                                'mvnoparent' => ''

                            );
                    }
                }
            }
        } else {
            return response('O token não foi autorizado', 401);
        }
    }
}
