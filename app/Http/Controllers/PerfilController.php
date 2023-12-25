<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('perfil.index', ['nombre' => auth()->user()->name]);
    }   //

    public function store(Request $request)
    {

        //Modificar el Request
        $request->request->add(['username' => Str::slug($request->username)]);


        // Validation
        //in:ejemplo obliga el uso de ese obligatorio
        //Validar que yo mismo sea
        $this->validate($request, [
            'username' => [
                'required', 'unique:users,username,' . auth()->user()->id, 'min:3', 'max:20',
                'not_in:twitter,editar-perfil'
            ],
            'email' => [
                'required', 'unique:users,email,' . auth()->user()->id, 'max:60'
            ]
        ]);

        if ($request->imagen) {

            $imagen = $request->file('imagen');

            $nombreImagen = Str::uuid() . "." . $imagen->extension();

            $imagenServidor = Image::make($imagen);

            $imagenServidor->fit(1000, 1000);

            $imagenPath = public_path('perfiles') . '/' . $nombreImagen;
            $imagenServidor->save($imagenPath);
            $input = $request->all();
        }
        //Guardar cambios

        $usuario = User::find(auth()->user()->id);
        $usuario->username = $request->username;
        $usuario->email = $request->email;
        $usuario->imagen = $nombreImagen ?? auth()->user()->imagen ?? null;
        $usuario->save();
        //Pendiente la contrasena

        //Redireccionar
        return redirect()->route('posts.index', $usuario->username);
    }
}
