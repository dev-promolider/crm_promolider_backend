<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class FreecourseController extends Controller
{
    // Ruta para visualizar la lista de alumnos según el id de la masterclass
    public function viewFreecourses()
    {
        return view('content.masterclass.freecourse.index');
    }

    // Ruta para mostrar el formulario de creación de un curso gratuito
    public function create()
    {
        $categories = Category::all();
        return view('content.masterclass.freecourse.create', compact('categories')); // Asegúrate de tener esta vista
    }

    public function store(Request $request)
    {
        // Lógica para guardar el curso
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
        ]);

        // Aquí deberías guardar los datos en la base de datos

        // Redirige a la lista de cursos gratuitos
        return redirect()->route('freecourse.index');
    }

}