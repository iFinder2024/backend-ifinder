<?php

namespace App\Http\Controllers;

use App\Models\CompanyModel;
use App\Models\CompanyPersonModel;
use App\Models\ConsultaFaturaModel;
use App\Models\CustomerModel;
use App\Models\IntegracaoModel;
use App\Models\MsisdnModel;
use App\Models\personModel;
use App\Models\RevendedorPjModel;
use App\Models\TokenSisModel;
use App\Models\TrocaTitularidadeModel;
use App\Models\UserSandBoxModel;
use App\Models\UsersModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class CadastrastraUserController extends Controller
{
    public function deletar(Request $request)
    {
        if (!isset($request->token)) {
            return response('O campo Token deve ser enviado', 590);
        }

        if ($this->checatoken($request->token) == true) {

            $user = new UsersModel();
            $userid = $user::where('cpf', $request->cpf)->first();
            $userid->profileid = '0';
            $userid->save();
            return $userid;
        } else {
            return response('O token não foi autorizado', 401);
        }
    }

    public function cadastraUsuario(Request $request)
    {
        //Validações de cadastro
        if (!$this->validacpf($request->cpf) == true) {
            return $this->validacpf($request->cpf);
        }

        if (!$this->ValidaEmail($request->email) == true) {
            return response('E-mail Inválido', 590);
        }

        if ($request->has('whatsapp')) {
            $whats = preg_replace('/[^0-9]/', '', $request->input('whatsapp'));

            // Verifique se o número possui mais de 11 dígitos antes de removê-los
            if (strlen($whats) > 11) {
                $whats = substr($whats, 0, 11);
            }
            // Agora a variável $phone contém no máximo 11 dígitos.
        }

        if (strlen($request->cep) > 8) {
            return response('O CEP deve conter até 8 caracteres', 590);
        }
        if (strlen($request->uf) > 2) {
            return response('O UF deve conter até 2 caracteres', 590);
        }
        if (strlen($request->cpf) > 11) {
            return response('O CPF deve conter até 11 caracteres sem caracteres especiais', 590);
        }

        $user = new UsersModel();
        $usuario = $user::where('cpf', $request->cpf)->first();

        if ((empty($usuario))) {
            $user->name = $request->name;
            $user->cpf = $request->cpf;

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            } else {
                $user->password = Hash::make('123a456b'); // SENHA PADRÃO
            }

            $user->email = $request->email;
            $user->nascimento = Carbon::parse($request->nascimento);
            
            if ($request->has('whatsapp')) {
                $user->whatsapp = $whats;
            }

            $user->cep = $request->cep;
            $user->rua = $request->rua;
            $user->cidade = $request->cidade;
            $user->uf = $request->uf;
            $user->numero_casa= $request->numero;
            $user->complemento = $request->complemento;
            $user->pais = $request->pais;
            $user->sexo = $request->sexo;
            $user->save();

            return response('Usuário cadastrado com sucesso', 200);
        } else {
            return response('Usuário já cadastrado', 401);
        }
    }

    public function cadastraUsuarioAdmin(Request $request)
    {
        //Validações de cadastro
        if (!$this->validacpf($request->cpf) == true) {
            return $this->validacpf($request->cpf);
        }

        if (!$this->ValidaEmail($request->email) == true) {
            return response('E-mail Inválido', 590);
        }

        if ($request->has('whatsapp')) {
            $whats = preg_replace('/[^0-9]/', '', $request->input('whatsapp'));

            // Verifique se o número possui mais de 11 dígitos antes de removê-los
            if (strlen($whats) > 11) {
                $whats = substr($whats, 0, 11);
            }
            // Agora a variável $phone contém no máximo 11 dígitos.
        }

        if (strlen($request->cep) > 8) {
            return response('O CEP deve conter até 8 caracteres', 590);
        }
        if (strlen($request->uf) > 2) {
            return response('O UF deve conter até 2 caracteres', 590);
        }
        if (strlen($request->cpf) > 11) {
            return response('O CPF deve conter até 11 caracteres sem caracteres especiais', 590);
        }

        $user = new UsersModel();
        $usuario = $user::where('cpf', $request->cpf)->first();

        if ((empty($usuario))) {
            $user->name = $request->name;
            $user->cpf = $request->cpf;

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            } else {
                $user->password = Hash::make('123a456b'); // SENHA PADRÃO
            }

            $user->email = $request->email;
            $user->nascimento = Carbon::parse($request->nascimento);
            
            if ($request->has('whatsapp')) {
                $user->whatsapp = $whats;
            }

            $user->cep = $request->cep;
            $user->rua = $request->rua;
            $user->cidade = $request->cidade;
            $user->uf = $request->uf;
            $user->numero_casa= $request->numero;
            $user->complemento = $request->complemento;
            $user->pais = $request->pais;
            $user->sexo = $request->sexo;

            $user->useradminid = $user::max('useradminid') + 1;
            $user->profileid = 3;
            $user->save();

            return response('Usuário Admin cadastrado com sucesso', 200);
        } else {
            return response('Usuário já cadastrado', 401);
        }
    }
}
