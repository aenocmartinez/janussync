<?php

namespace App\Actions;

use App\Contracts\HasModel;
use App\DataTransferObjects\UserDTO;
use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\UserCreationDetail;
use Exception;
use Illuminate\Support\Facades\Log;

class SyncUsersAction extends SyncActionBase implements HasModel
{
    public static function getModelClass(): string
    {
        return UserCreationDetail::class;
    }


    public function handle()
    {
        $details = '';
        $createdUsersCount = 0;
        $excludedUsersCount = 0; // Contador de usuarios sin email
        $excludedUsersList = []; // Lista de usuarios excluidos

        try {

            if (!$this->verifyConnections([
                Academusoft::class => 'Academusoft',
                BrightSpace::class => 'BrightSpace'
            ])) {
                return;
            }

            $users = Academusoft::getUsers();

            $existingEmails = UserCreationDetail::whereIn('email', $users->pluck('email'))->pluck('email')->toArray();

            // Separar usuarios válidos y usuarios con email NULL
            $filteredUsers = $users->filter(fn(UserDTO $user) => !in_array($user->email, $existingEmails) && !empty($user->email));
            $excludedUsers = $users->filter(fn(UserDTO $user) => empty($user->email));

            // Contador y lista de usuarios excluidos
            $excludedUsersCount = $excludedUsers->count();
            $excludedUsersList = $excludedUsers->map(fn(UserDTO $user) => "{$user->nombres} {$user->apellidos}")->toArray();

            // Depuración: Registrar en logs los usuarios con email NULL
            if ($excludedUsersCount > 0) {
                Log::warning("Usuarios con email NULL detectados y excluidos:", $excludedUsers->toArray());
            }

            // Transformar usuarios a formato de inserción
            $newUserDetails = $filteredUsers->map(fn(UserDTO $user) => [
                'first_name' => $user->nombres,
                'last_name' => $user->apellidos,
                'email' => $user->email,
                'role' => $user->rol_usuario,
                'scheduled_task_id' => $this->scheduledTask->id,
            ])->toArray();

            if (!empty($newUserDetails)) {
                UserCreationDetail::insert($newUserDetails);
                $createdUsersCount = count($newUserDetails);
            }

            // Construcción del mensaje de ejecución
            $details = "Sincronización de usuarios completada con éxito. Se crearon {$createdUsersCount} usuarios.";
            
            if ($excludedUsersCount > 0) {
                $excludedUsersString = implode(', ', array_slice($excludedUsersList, 0, 5)); // Mostrar solo 5 para no sobrecargar
                if ($excludedUsersCount > 5) {
                    $excludedUsersString .= "... y otros " . ($excludedUsersCount - 5) . " usuarios.";
                }
                $details .= " {$excludedUsersCount} usuarios fueron omitidos porque no tenían email: {$excludedUsersString}";
            }

            $this->logTask(true, $details);

        } catch (Exception $e) {
            $details = 'Error al sincronizar usuarios: ' . $e->getMessage();
            $this->logTask(false, $details);
        }
    }
  
}
