<?php

namespace App\Http\Controllers;

use App\Models\BlockedIP;
use App\Models\CompanyModel;
use App\Models\ConsultaFaturaModel;
use App\Models\IntegracaoModel;
use App\Models\LoginModel;
use App\Models\personModel;
use App\Models\RevendedorPjModel;
use App\Models\TokenSisModel;
use App\Models\UserMultinivelModel;
use App\Models\UsersModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Exception;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Exists;

class LoginController extends Controller
{
    public function AlteraSenha(Request $request)
    {
        if (!isset($request->token) || $request->token == '') {
            return response('É Necessário enviar o parâmetro Token', 590);
        }
        if (!isset($request->cpf) || $request->cpf == '') {
            return response('É Necessário enviar o parâmetro cpf', 590);
        }
        if (!isset($request->password) || $request->password == '') {
            return response('É Necessário enviar o parâmetro password', 590);
        }

        if ($this->checatoken($request->token) == true) {

            $password = Hash::make($request->password);
            $log = new UsersModel();
            $login = $log::where('cpf', '=', $request->cpf)->get();

            $telefone = new ConsultaFaturaModel();
            $telefoneFranquia = new RevendedorPjModel();
            //envia senha via sms

            $msisdn = $telefone::where('cpf', '=', $request->cpf)->get();

            $text = 'Parabéns, sua senha foi alterada com sucesso. Não compartilhe sua senha com ninguém, ela é de seu uso exclusivo!';

            if ($msisdn->isnotEmpty()) {
                try {
                    $this->EnviaWhatsapp($request->cpf, $text);
                } catch (Exception $e) {
                    return response($e->getMessage(), 500);
                }
            } else {
            }

            //Salva senha

            $att = $log::where('cpf', '=', $request->cpf)->first();
            $att->password = $password;
            $att->trocarsenha = false;
            $att->save();

            return response('Senha alterada!', 230);
        } else {
            return response('O token não foi autorizado', 401);
        }
    }

    public function AlteraSenhaPeloEmail(Request $request)
    {
        if (!isset($request->cpf) || $request->cpf == '') {
            return response('É Necessário enviar o parâmetro cpf', 590);
        }
        if (!isset($request->password) || $request->password == '') {
            return response('É Necessário enviar o parâmetro password', 590);
        }

        $password = Hash::make($request->password);
        $log = new UsersModel();
        $login = $log::where('cpf', $request->cpf)->get();

        $telefone = new ConsultaFaturaModel();

        $text = 'Parabéns, sua senha foi alterada com sucesso. Não compartilhe sua senha com ninguém, ela é de seu uso exclusivo!';

        if ($login[0]['profileid'] === 6) {
            $revendedorPj = new RevendedorPjModel();
            $revendedor = $revendedorPj::where('cnpj', $request->cpf)->first();

            try {
                $this->EnviaWhatsappEndPoint($request->cpf, $revendedor->celular, $text);
            } catch (Exception $e) {
                return response($e->getMessage(), 500);
            }
        } else {
            try {
                $this->EnviaWhatsapp($request->cpf, $text);
            } catch (Exception $e) {
                return response($e->getMessage(), 500);
            }
        }

        //Salva senha

        $att = $log::where('cpf', $request->cpf)->first();
        $att->password = $password;
        $att->trocarsenha = false;
        $att->save();

        return response('Senha alterada', 230);
    }

    // ALTERAR SENHA DO CLIENTE
    public function alterarsenhacliente(Request $request)
    {
        $log = new UsersModel();
        /*
        A REQUISIÇÃO DEVE CONTER:
        TOKEN
        CPF
        NOVA SENHA
         */
        
        $log = new UsersModel();
        $token = new TokenSisModel();

        if (!isset($request->token) || $request->token == '') {
            return response('É Necessário enviar o parâmetro Token', 590);
        }
        if (!isset($request->cpf) || $request->cpf == '') {
            return response('É Necessário enviar o parâmetro cpf', 591);
        }
        if (!isset($request->password) || $request->password == '') {
            return response('É Necessário enviar o parâmetro password', 592);
        }

        $profile = $log::Where('cpf', '=', $request->cpf)->first('profileid');

        if ($profile->profileid != 3) {
            return response('O CPF não pertence a um cliente', 593);
        }

        $password = Hash::make($request->password);
        $login = $log::where('cpf', '=', $request->cpf)->get();

        //envia senha via sms

        $parceiro = $telefone::where('cpf', '=', $request->cpf)->first('parceiro');

        $checatoken = $token::where('token', '=', $request->token)->first('parceiro');

        if ($checatoken->parceiro == $parceiro->parceiro) {
            $msisdn = $telefone::where('cpf', '=', $request->cpf)->get();

            $text = 'Parabéns, sua senha foi alterada com sucesso. Não compartilhe sua senha com ninguém, ela é de seu uso exclusivo!';

            if ($msisdn->isnotEmpty()) {
                try {
                    $this->EnviaWhatsapp($request->cpf, $text);
                } catch (Exception $e) {
                    return response($e->getMessage(), 500);
                }
            } else {
            }
            //Salva senha
            $att = $log::where('cpf', '=', $request->cpf)->first();
            $att->password = $password;
            $att->trocarsenha = false;
            $att->save();

            return response('Senha alterada!', 230);
        } else {
            return response('Este CPF ou CNPJ não pertence à esta operadora', 401);
        }
    }

    public function verificartoken(Request $request)
    {
        if (!isset($request->tokenesquecisenha) || $request->tokenesquecisenha == '') {
            return response('É Necessário enviar o parâmetro token', 590);
        }

        $log = new UsersModel();
        $verifica = $log::where('cpf', '=', $request->cpf)->get('tokenesquecisenha');

        if ($request->tokenesquecisenha == $verifica[0]['tokenesquecisenha']) {
            return response('Token validado com sucesso', 200);
        } else {
            return response('Token não válido', 401);
        }
    }

    public function esqueciMinhaSenhaEmail(Request $request)
    {
        if (!isset($request->cpf) || $request->cpf == '') {
            return response('É Necessário enviar o parâmetro cpf', 590);
        }

        $log = new UsersModel();
        $login = $log::where('cpf', '=', $request->cpf)->first();
        if ($login->profileid == 0) {
            return response('Cadastro Excluido', 590);
        }

        // if ($request->cpf == '058521789124' || $request->cpf == '05628869155') {
        //     $login->ultimoip = $request->ip();
        //     $login->esquecisenha = Carbon::now();
        //     $login->save();

        //     $this->EnviaWhatsappPj('8955170110114542010', 'Tentativa de alterar senha do cpf: ' . $request->cpf . 'negada, IP de Origem : ' . $request->ip());

        //     return Response('Ops, você não tem autorização para alterar senha neste usuário', 501);
        // }

        $login->ultimoip = $request->ip();
        $login->esquecisenha = Carbon::now();
        $login->save();

        $userModel = new UsersModel();

        $rand = rand(100000, 999999);
        $link = "https://ifinder.com.br/#/alterar-senha";
        $att = $log::where('cpf', $request->cpf)->first();
        $att->tokenesquecisenha = $rand;
        $att->save();
        $text = 'Olá! ' . $login->name;

        $user = $userModel::where('cpf', $request->cpf)->first();
        if ($user) {
            $msisdn = $userModel::whereNotNull('whatsapp')->where('cpf', $request->cpf)->get();
            $email = $msisdn[0]['email'];
            $cellphone = $msisdn[0]['whatsapp'];
            $whatsapp = $msisdn[0]['whatsapp'];
            $name = $msisdn[0]['name'];
            $parceiro = $msisdn[0]['parceiro'];
            $cnpj = $request->cpf;
        }

        $textoSMS = 'Recebemos uma solicitacao para redefinir a senha da sua conta da. Para continuar com o processo de redefinicao, clique no link: ' . $link . ' e insira o seguinte token: ' . $rand;

        if ($request->tipoDeEnvio == 'email') {
            $this->enviarEmailEsqueciSenha(
                $name,
                'email_template_mudarsenha',
                $text,
                $rand,
                $email,
                $name,
                'Redefinição de senha',
                'desenvolvimento@ifinder.com.br',
                'iFinder Brasil',
                $link,
                $cnpj
            );
            return ($email);
        }

        if ($request->tipoDeEnvio == 'whatsapp') {
            if ($user->profileid === 6) {
                $this->EnviaWhatsappEndPoint($request->cpf, $whatsapp, $textoSMS);
                $this->EnviaWhatsappEndPoint($request->cpf, $whatsapp, $textoSMS);
                return ($whatsapp);
            }
            //Envio do link pelo Whatsapp
            $this->EnviaWhatsapp($request->cpf, $textoSMS);
            $this->EnviaWhatsapp($request->cpf, $textoSMS);
            return ($whatsapp);
        }

        if ($request->tipoDeEnvio == 'sms') {
            $this->SendSMSSendPulse($this->verificatelefone($cellphone), $textoSMS);
            return ($cellphone);
        }
    }

    public function TesteToken(Request $request)
    {
        // return $this->checatoken($request->token);
        if ($this->checatoken($request->token) == true) {
            return 'acessando';
        } else {
            return response('Token Invalido', 401);
        }
    }

    public function ParceirosAtrasados()
    {
        set_time_limit(8000000);

        $companyModel = new CompanyModel();
        $cnpjtb = $companyModel::all(['tradename', 'cnpj']);

        $resp = [];
        foreach ($cnpjtb as $cnpj) {
            return $pj = $this->liberaloginPJ('37948330000113');
            $pj = $this->vencimentoFatParceiro($cnpj->cnpj);
            $status = $pj[0]['vencido'];

            if ($status >= 1) {
                $resp[] = [
                    'parceiro' => $cnpj->tradename,
                    'cnpj' => $cnpj->cnpj,
                    // 'vencimento' => $pj[0]['dataVencimento'] 
                ];
            }
        }

        return count($resp);
    }

    public function VerificaPagamentoToken()
    {
        set_time_limit(8000000);

        // $dataAtual = Carbon::parse('2024-06-26 10:50:42.000')->format('Y-m-d');
        $dataAtual = Carbon::now()->format('Y-m-d');

        // Busca todos os tokens onde a data de previsão já passou e não foram desbloqueados
        $tokens = TokenSisModel::where('previsao_pagamento', '<', $dataAtual)
            ->get();

        if ($tokens->isempty()) {
            return response('Nenhum token pendente', 200);
        }

        foreach ($tokens as $token) {
            $company = CompanyModel::where('companyid', $token->companyid)->first('cnpj');
            $pj = $this->liberaloginPJ($company->cnpj);
            $status = $pj[0]['vencido'];

            if ($status >= 1) {
                $token->bloqueio = 1;
                $token->save();
            } else {
                $token->bloqueio = 0;
                $token->previsao_pagamento = null;
                $token->save();
            }
        }

        return response('Verificação de pagamentos concluída.', 200);
    }

    // public function esqueciMinhaSenhaEmail(Request $request)
    // {
    //     if (!isset($request->cpf) || $request->cpf == '') {
    //         return response('É Necessário enviar o parâmetro cpf', 590);
    //     }

    //     $password = Hash::make($request->password);
    //     $log = new LoginModel();
    //     $login = $log::where('cpf', '=', $request->cpf)->first();
    //     if ($login->profileid == 5) {
    //         return response('Cadastro Excluido', 590);
    //     }

    //     if ($request->cpf == '01269952145' || $request->cpf == '70941718115') {
    //         $login->ultimoip = $request->ip();
    //         $login->esquecisenha = Carbon::now();
    //         $login->save();

    //         //MENSAGEM PARA O ILBER
    //         //$this->EnviaWhatsapp('01269952145', 'Tentativa de alterar senha negada ILBER, IP de Origem : ' . $request->ip());

    //         //MENSAGEM PARA O IRLAN
    //         $this->EnviaWhatsappPj('8955170110114542010', 'Tentativa de alterar senha do cpf: ' . $request->cpf . 'negada, IP de Origem : ' . $request->ip());

    //         return Response('Ops , você não tem autorização para alterar senha neste usuário', 501);
    //     }

    //     $login->ultimoip = $request->ip();
    //     $login->esquecisenha = Carbon::now();
    //     $login->save();
    //     $telefone = new ConsultaFaturaModel();
    //     $userModel = new UsuarioModel();
    //     $companyModel = new CompanyModel();
    //     $rand = rand(100000, 999999);
    //     $link = "https://operadora.app.br/#/alterar-senha-email";
    //     $att = $log::where('cpf', '=', $request->cpf)->first();
    //     $att->tokenesquecisenha = $rand;
    //     $att->save();
    //     $text = 'Olá! ' . $login->name;

    //     $user = $userModel::where('cpf', $request->cpf)->first();
    //     if ($user->profileid === 6) {
    //         $revendedorPj = new RevendedorPjModel();
    //         $revendedor = $revendedorPj::where('cnpj', $request->cpf)->first();
    //         $parceiro = $companyModel::where('companyid', $revendedor->parentcompanyid)->first('tradename');

    //         $email = $revendedor->email;
    //         $cellphone = $revendedor->celular;
    //         $whatsapp = $revendedor->celular;
    //         $name = $revendedor->tradename;
    //         $cnpj = $request->cpf;
    //     } else {
    //         $msisdn = $telefone::whereNotNull('msisdn')->where('cpf', $request->cpf)->get();
    //         $email = $msisdn[0]['email'];
    //         $cellphone = $msisdn[0]['cellphone'];
    //         $whatsapp = $msisdn[0]['whatsapp'];
    //         $name = $msisdn[0]['name'];
    //         $parceiro = $msisdn[0]['parceiro'];
    //         $cnpj = $request->cpf;
    //     }

    //     $texto = 'Recebemos uma solicitação para redefinir a senha da sua conta da ' . $parceiro . '. Se você não solicitou a redefinição de senha, ignore esta mensagem e nenhuma alteração será feita em sua conta. Lembre-se de que este link é válido por 30 minutos para sua segurança. Após expirar, você precisará solicitar uma nova redefinição de senha. Se você tiver alguma dúvida ou precisar de assistência adicional, entre em contato com nossa equipe de suporte. Para continuar com o processo de redefinição, clique no link: ' . $link . ' e insira o seguinte token: ' . $rand;

    //     $textoSMS = 'Recebemos uma solicitacao para redefinir a senha da sua conta da ' . $parceiro . '. Para continuar com o processo de redefinicao, clique no link: ' . $link . ' e insira o seguinte token: ' . $rand;

    //     if ($request->tipoDeEnvio == 'email') {
    //         $this->enviarEmailEsqueciSenha(
    //             $name,
    //             'email_template_mudarsenha',
    //             $text,
    //             $rand,
    //             $email,
    //             $name,
    //             'Redefinição de senha',
    //             'alterasenha@comunicacao.operadoravirtual.com.br',
    //             $parceiro,
    //             $parceiro,
    //             $link,
    //             $cnpj
    //         );
    //         return ($email);
    //     }
    // }
}
