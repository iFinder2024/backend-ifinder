<?php

namespace App\Http\Controllers;

use App\Models\TokenRequestModel;
use App\Models\TokenSisModel;
use App\Models\UsersModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class TokenSisController extends Controller
{
    public function geratoken(Request $request)
    {
        $token = new TokenSisModel();
        $user = new UsersModel();

        $parceiro = $token::where('tradename', $request->parceiro)->first(['id']);
        $getuser = $user::where('cpf', $request->cpf)->first('useradminid');

        $better_token = bin2hex(random_bytes(25));
        
        if($parceiro){
            $gettoken = $token::find($parceiro->id);
            $gettoken->update(['token' => $better_token, 'useradminid' => $getuser->useradminid]);       
        } else {
            $token->tradename = $request->parceiro;
            $token->token = $better_token;
            $token->useradminid = $getuser->useradminid;
            $token->save();
        }

        return response(['token' => $better_token], 201);
    }

    public function reativarToken(Request $request)
    {
        if (!isset($request->token) || $request->token == '') {
            return response('É Necessário enviar o parâmetro Token', 590);
        }

        $userModel = new UsuarioModel();
        $tokensisModel = new TokenSisModel();
        $token = $tokensisModel::where('companyid', 46)->first();

        if ($request->token === $token->token) {
            $userInforeq = $request->userInfo;
            $jsonUserInfo = json_decode($userInforeq, true);
            $cpfUserInfo = $jsonUserInfo['cpf'];

            if (
                $cpfUserInfo === '02767237163' ||
                $cpfUserInfo === '70941718115' ||
                $cpfUserInfo === '01269952145' ||
                $cpfUserInfo === '05852179124' ||
                $cpfUserInfo === '08007069194' ||
                $cpfUserInfo === '02218032112'
            ) {
                $tokencidget = $tokensisModel::where('companyid', $request->companyid)->get();

                // return $dataprevisao = Carbon::parse($request->data_previsao)->format('d-m-Y');
                if ($tokencidget->isempty()) {
                    return response('Token não encontrado', 404);
                }

                foreach ($tokencidget as $tokencid) {
                    if ($request->block == 0) {
                        if ($tokencid->block_manual == 1) {
                            $tokencid->bloqueio = 0;
                            $tokencid->save();

                            $response = 'Token desbloqueado';
                            $code = 200;
                        } else {
                            if ($tokencid->tipo_bloqueio == 'AUTOMATICO') {
                                // SALVA DATA DE PREVISAO
                                if (!isset($tokencid->previsao_pagamento)) {
                                    $tokencid->previsao_pagamento = Carbon::parse($request->data_previsao)->format('Y-m-d');
                                    $tokencid->save();

                                    // DATA ATUAL
                                    // $dataAtual = Carbon::parse('2024-06-26 11:43:27.000')->format('Y-m-d');
                                    $dataAtual = Carbon::now()->format('Y-m-d');

                                    // DATA PREVISAO
                                    $previsaoPagamento = Carbon::parse($tokencid->previsao_pagamento);

                                    // VERIFICA SE PREVISAO DE PAGAMENTO É SUPERIOR A DATA ATUAL
                                    if ($previsaoPagamento->lessThan($dataAtual)) {
                                        $response = 'Token não pode ser desbloqueado';
                                        $code = 401;
                                    } else {
                                        if ($tokencid->bloqueio == 0) {
                                            $response = 'Token já está desbloqueado';
                                            $code = 200;
                                        } else {
                                            $tokencid->bloqueio = 0;
                                            $tokencid->save();

                                            $response = 'Token desbloqueado';
                                            $code = 200;
                                        }
                                    }
                                } else {
                                    $previsaoPagamento = Carbon::parse($tokencid->previsao_pagamento)->format('d-m-Y');
                                    $response = "Já existe uma Previsão de Pagamento pendente: {$previsaoPagamento}";
                                    $code = 401;
                                }
                            }
                        }
                    } else {
                        if ($tokencid->bloqueio == 1) {
                            $response = 'Token já está bloqueado';
                            $code = 401;
                        } else {
                            if ($tokencid->block_manual == 1) {
                                $tokencid->bloqueio = 1;
                                $tokencid->save();
                            } else {
                                $tokencid->bloqueio = 1;
                                $tokencid->tipo_bloqueio = 'MANUAL';
                                $tokencid->block_manual = 1;
                                $tokencid->save();
                            }

                            $response = 'Token bloqueado';
                            $code = 200;
                        }
                    }
                }

                return response($response, $code);
            } else {
                $userid = $userModel::where('cpf', $cpfUserInfo)->first('userid');

                // Validar a solicitação
                $request->validate([
                    'companyid' => 'required|integer',
                    'data_previsao' => 'required|date',
                ]);

                // Criar uma nova solicitação de token
                $tokenRequest = new TokenRequestModel();
                $tokenRequest->companyid = $request->companyid;
                $tokenRequest->userid = $userid->userid;
                $tokenRequest->previsao_pagamento = $request->data_previsao;
                $tokenRequest->status = 'PENDING';
                $tokenRequest->save();

                return response('Solicitação de Reativação Token criada com sucesso', 201);
            }
        } else {
            return response('Token Invalido', 401);
        }
    }

    public function requestToken(Request $request)
    {
        if (!isset($request->token) || $request->token == '') {
            return response('É Necessário enviar o parâmetro Token', 590);
        }

        $tokensisModel = new TokenSisModel();
        $userModel = new UsuarioModel();
        $token = $tokensisModel::where('companyid', 46)->first();

        if ($request->token === $token->token) {
            $userInforeq = $request->userInfo;
            $jsonUserInfo = json_decode($userInforeq, true);
            $cpfUserInfo = $jsonUserInfo['cpf'];

            $userid = $userModel::where('cpf', $cpfUserInfo)->first('userid');

            // Validar a solicitação
            $request->validate([
                'companyid' => 'required|integer',
                'data_previsao' => 'required|date',
            ]);

            // Criar uma nova solicitação de token
            $tokenRequest = new TokenRequestModel();
            $tokenRequest->companyid = $request->companyid;
            $tokenRequest->userid = $userid->userid;
            $tokenRequest->previsao_pagamento = $request->data_previsao;
            $tokenRequest->status = 'PENDING';
            $tokenRequest->save();

            return response('Solicitação de Reativação Token criada com sucesso', 201);
        } else {
            return response('Token Inválido', 401);
        }
    }

    public function approveToken(Request $request)
    {
        if (!isset($request->token) || $request->token == '') {
            return response('É Necessário enviar o parâmetro Token', 590);
        }

        $tokensisModel = new TokenSisModel();
        $token = $tokensisModel::where('companyid', 46)->first();

        if ($request->token === $token->token) {
            $userInforeq = $request->userInfo;
            $jsonUserInfo = json_decode($userInforeq, true);
            $cpfUserInfo = $jsonUserInfo['cpf'];

            if (
                $cpfUserInfo === '02767237163' ||
                $cpfUserInfo === '70941718115' ||
                $cpfUserInfo === '01269952145' ||
                $cpfUserInfo === '05852179124' ||
                $cpfUserInfo === '08007069194' ||
                $cpfUserInfo === '02218032112'
            ) {
                $tokensisModel = new TokenSisModel();

                // Validar a solicitação
                $request->validate([
                    'request_id' => 'required|integer',
                    'approved' => 'required|boolean',
                ]);

                // Encontrar a solicitação de token
                $tokenRequest = TokenRequestModel::find($request->request_id);

                if (!$tokenRequest) {
                    return response('Solicitação não encontrada', 404);
                }

                if ($request->approved) {
                    // Atualizar o status do token
                    $tokens = $tokensisModel::where('companyid', $tokenRequest->companyid)->get();

                    foreach ($tokens as $token) {
                        $token->bloqueio = 0;
                        $token->previsao_pagamento = $tokenRequest->previsao_pagamento;
                        $token->save();
                    }

                    $tokenRequest->status = 'APPROVED';
                    $tokenRequest->save();

                    return response('Token aprovado e desbloqueado', 200);
                } else {
                    $tokenRequest->status = 'DENIED';
                    $tokenRequest->save();

                    return response('Solicitação de token negada', 200);
                }
            } else {
                return response('Não autorizado', 401);
            }
        } else {
            return response('Token Invalido', 401);
        }
    }
}
