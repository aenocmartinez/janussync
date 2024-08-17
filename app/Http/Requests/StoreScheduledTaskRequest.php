<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduledTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'task_name' => 'required|string|max:255',
            'frequency' => 'required|string|in:Diaria,Semanal,Mensual,Personalizada',
            'action' => 'required|string|in:' . implode(',', array_keys(config('scheduled_task_actions.actions'))), // Validar que la acción esté en la lista de acciones configuradas
        ];

        $rules = array_merge($rules, $this->getFrequencySpecificRules());

        return $rules;
    }

    protected function getFrequencySpecificRules()
    {
        if ($this->input('frequency') == 'Diaria') {
            return $this->dailyRules();
        } elseif ($this->input('frequency') == 'Semanal') {
            return $this->weeklyRules();
        } elseif ($this->input('frequency') == 'Mensual') {
            return $this->monthlyRules();
        } elseif ($this->input('frequency') == 'Personalizada') {
            return $this->customRules();
        }

        return [];
    }

    protected function dailyRules()
    {
        return [
            'execution_time_daily' => 'required|date_format:H:i',
        ];
    }

    protected function weeklyRules()
    {
        return [
            'execution_time_weekly' => 'required|date_format:H:i',
            'day_of_week' => 'required|string|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
        ];
    }

    protected function monthlyRules()
    {
        return [
            'execution_time_monthly' => 'required|date_format:H:i',
            'day_of_month' => 'required|integer|min:1|max:31',
        ];
    }

    protected function customRules()
    {
        return [
            'execution_time_custom' => 'required|date_format:H:i',
            'custom_date' => 'required|date_format:Y-m-d',
        ];
    }
}
