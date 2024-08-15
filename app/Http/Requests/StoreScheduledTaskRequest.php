<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduledTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permitir que cualquier usuario autorizado envíe este formulario
    }

    public function rules()
    {
        // Reglas comunes a todos los formularios
        $rules = [
            'task_name' => 'required|string|max:255',
            'frequency' => 'required|string|in:Diaria,Semanal,Mensual,Personalizada',
        ];

        // Agregar reglas condicionales dependiendo de la frecuencia
        $rules = array_merge($rules, $this->getFrequencySpecificRules());

        return $rules;
    }

    /**
     * Obtener las reglas específicas según la frecuencia seleccionada.
     *
     * @return array
     */
    protected function getFrequencySpecificRules()
    {
        switch ($this->input('frequency')) {
            case 'Diaria':
                return $this->dailyRules();
            case 'Semanal':
                return $this->weeklyRules();
            case 'Mensual':
                return $this->monthlyRules();
            case 'Personalizada':
                return $this->customRules();
            default:
                return [];
        }
    }

    /**
     * Reglas para la frecuencia diaria.
     *
     * @return array
     */
    protected function dailyRules()
    {
        return [
            'execution_time' => 'required|date_format:H:i',
        ];
    }

    /**
     * Reglas para la frecuencia semanal.
     *
     * @return array
     */
    protected function weeklyRules()
    {
        return [
            'day_of_week' => 'required|string|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'execution_time' => 'required|date_format:H:i',
        ];
    }

    /**
     * Reglas para la frecuencia mensual.
     *
     * @return array
     */
    protected function monthlyRules()
    {
        return [
            'day_of_month' => 'required|integer|min:1|max:31',
            'execution_time' => 'required|date_format:H:i',
        ];
    }

    /**
     * Reglas para la frecuencia personalizada.
     *
     * @return array
     */
    protected function customRules()
    {
        return [
            'custom_date' => 'required|date_format:Y-m-d',
            'custom_time' => 'required|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'task_name.required' => 'El nombre de la tarea es obligatorio.',
            'frequency.required' => 'La frecuencia es obligatoria.',
            'execution_time.required' => 'La hora de ejecución es obligatoria.',
            'day_of_week.required' => 'El día de la semana es obligatorio.',
            'day_of_month.required' => 'El día del mes es obligatorio.',
            'custom_date.required' => 'La fecha es obligatoria para la frecuencia personalizada.',
            'custom_time.required' => 'La hora es obligatoria para la frecuencia personalizada.',
        ];
    }
}
