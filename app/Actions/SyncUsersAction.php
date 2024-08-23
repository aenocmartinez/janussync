<?php

namespace App\Actions;

use App\DataTransferObjects\UserDTO;
use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\UserCreationDetail;
use Exception;

class SyncUsersAction extends SyncActionBase
{
    public function handle()
    {
        $details = '';
        $createdUsersCount = 0;
    
        try {

            // Verificar conexión con Academusoft y BrightSpace
            if (!$this->verifyConnections([
                Academusoft::class => 'Academusoft',
                BrightSpace::class => 'BrightSpace'
            ])) {
                return;
            }
    
            $users = Academusoft::getUsers();
    
            $existingEmails = UserCreationDetail::whereIn('email', $users->pluck('email'))->pluck('email')->toArray();
    
            $newUserDetails = $users->filter(function (UserDTO $user) use ($existingEmails) {
                return !in_array($user->email, $existingEmails);
            })->map(function (UserDTO $user) {
                return [
                    'first_name' => $user->name,
                    'last_name' => $user->name,
                    'email' => $user->email,
                    'scheduled_task_id' => $this->scheduledTask->id,
                ];
            })->toArray();
    
            if (!empty($newUserDetails)) {
                UserCreationDetail::insert($newUserDetails);
                $createdUsersCount = count($newUserDetails);
            }
    
            $details = "Sincronización de usuarios completada con éxito. Se crearon {$createdUsersCount} usuarios.";
            $this->logTask(true, $details);
    
        } catch (Exception $e) {
            $details = 'Error al sincronizar usuarios: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }    
}
