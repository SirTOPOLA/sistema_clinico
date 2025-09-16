<div class="modal fade" id="modalEditPersonal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar Personal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditPersonal" action="api/actualizar_personal.php" method="post">
          <input type="hidden" name="action" value="edit_personal">
          <input type="hidden" name="id" id="edit_personal_id">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="edit_nombrePersonal" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="edit_nombrePersonal" name="nombre" required>
            </div>
            <div class="col-md-6">
              <label for="edit_apellidosPersonal" class="form-label">Apellidos</label>
              <input type="text" class="form-control" id="edit_apellidosPersonal" name="apellidos" required>
            </div>
            <div class="col-md-6">
              <label for="edit_fechaNacimientoPersonal" class="form-label">Fecha de Nacimiento</label>
              <input type="date" class="form-control" id="edit_fechaNacimientoPersonal" name="fecha_nacimiento">
            </div>
            <div class="col-md-6">
              <label for="edit_telefonoPersonal" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="edit_telefonoPersonal" name="telefono">
            </div>
            <div class="col-md-12">
              <label for="edit_especialidadPersonal" class="form-label">Especialidad</label>
              <input type="text" class="form-control" id="edit_especialidadPersonal" name="especialidad">
            </div>
            <div class="col-md-12">
              <label for="edit_direccionPersonal" class="form-label">Dirección</label>
              <textarea class="form-control" id="edit_direccionPersonal" name="direccion"></textarea>
            </div>
            <div class="col-md-12">
              <label for="edit_correoPersonal" class="form-label">Correo</label>
              <input type="email" class="form-control" id="edit_correoPersonal" name="correo">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formEditPersonal" class="btn btn-primary rounded-pill"><i class="bi bi-save me-2"></i> actualizar Cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Editar Usuario -->
<div class="modal fade" id="modalEditUsuario" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditUsuario" action="api/actualizar_usuarios.php" method="post">
          <input type="hidden" name="action" value="edit_usuario">
          <input type="hidden" name="id_usuario" value="<?= (int) htmlspecialchars($_SESSION['usuario']['id']); ?>" id="edit_usuario_id">
          <div class="mb-3">
            <label for="edit_nombreUsuario" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" id="edit_nombreUsuario" name="nombre_usuario" required>
          </div>
          <div class="mb-3">
            <label for="edit_passwordUsuario" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
            <input type="password" class="form-control" id="edit_passwordUsuario" name="password">
          </div>
          <div class="mb-3">
            <label for="edit_rolUsuario" class="form-label">Rol</label>
            <select class="form-select" id="edit_rolUsuario" name="id_rol" required>
               <?php 
                $rolList = getTableData($pdo, 'roles', 100);
                foreach($rolList as $r): ?>
                  <option value="<?php echo htmlspecialchars($r['id']); ?>">
                    <?php echo htmlspecialchars($r['nombre'] ); ?>
                  </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_personalAsociado" class="form-label">Personal Asociado</label>
            <select class="form-select" id="edit_personalAsociado" name="id_personal" required>
              <?php 
                $personalList = getTableData($pdo, 'personal', 100);
                foreach($personalList as $p): ?>
                  <option value="<?php echo htmlspecialchars($p['id']); ?>">
                    <?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']); ?>
                  </option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formEditUsuario" class="btn btn-primary rounded-pill"><i class="bi bi-save me-2"></i> actualizar Cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Editar Paciente -->
<div class="modal fade" id="modalEditPaciente" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditPaciente" action="api/actualizar_paciente.php" method="post">
          <input type="hidden" name="action" value="edit_paciente">
          <input type="hidden" name="id" id="edit_paciente_id">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="edit_nombrePaciente" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="edit_nombrePaciente" name="nombre" required>
            </div>
            <div class="col-md-6">
              <label for="edit_apellidosPaciente" class="form-label">Apellidos</label>
              <input type="text" class="form-control" id="edit_apellidosPaciente" name="apellidos" required>
            </div>
            <div class="col-md-6">
              <label for="edit_fechaNacimientoPaciente" class="form-label">Fecha de Nacimiento</label>
              <input type="date" class="form-control" id="edit_fechaNacimientoPaciente" name="fecha_nacimiento">
            </div>
            <div class="col-md-6">
              <label for="edit_sexoPaciente" class="form-label">Sexo</label>
              <select class="form-select" id="edit_sexoPaciente" name="sexo">
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="edit_telefonoPaciente" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="edit_telefonoPaciente" name="telefono">
            </div>
            <div class="col-md-6">
              <label for="edit_emailPaciente" class="form-label">Email (OPCIONAL)</label>
              <input type="email" class="form-control" id="edit_emailPaciente" name="email">
            </div> 
            <div class="col-md-6">
              <label for="edit_dipPaciente" class="form-label">DIP</label>
              <input type="text" class="form-control" id="edit_dipPaciente" name="dip">
            </div>
            <div class="col-md-6">
              <label for="edit_profesionPaciente" class="form-label">Profesión</label>
              <input type="text" class="form-control" id="edit_profesionPaciente" name="profesion">
            </div>
            <div class="col-md-6">
              <label for="edit_ocupacionPaciente" class="form-label">Ocupación</label>
              <input type="text" class="form-control" id="edit_ocupacionPaciente" name="ocupacion">
            </div>
            <div class="col-md-6">
              <label for="edit_tutorNombrePaciente" class="form-label">Nombre del Tutor</label>
              <input type="text" class="form-control" id="edit_tutorNombrePaciente" name="tutor_nombre">
            </div>
            <div class="col-md-6">
              <label for="edit_telefonoTutorPaciente" class="form-label">Teléfono del Tutor</label>
              <input type="tel" class="form-control" id="edit_telefonoTutorPaciente" name="telefono_tutor">
            </div>
            <div class="col-md-12">
              <label for="edit_direccionPaciente" class="form-label">Dirección</label>
              <textarea class="form-control" id="edit_direccionPaciente" name="direccion"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formEditPaciente" class="btn btn-primary rounded-pill"><i class="bi bi-save me-2"></i> actualizar Cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Historial Clínico -->
<div class="modal fade" id="modalHistorialPaciente" tabindex="-1">
  <div class="modal-dialog modal-fullscreen-lg-down modal-xl">
    <div class="modal-content">
      <div class="modal-header d-print-none">
        <h5 class="modal-title" id="historialPacienteTitulo">Historial Clínico</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="a4-document" id="historialContenido">
          <!-- El contenido del historial se cargará aquí con JavaScript -->
        </div>
      </div>
      <div class="modal-footer d-print-none">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary rounded-pill" onclick="window.print()"><i class="bi bi-printer me-2"></i> Imprimir</button>
      </div>
    </div>
  </div>
</div>
