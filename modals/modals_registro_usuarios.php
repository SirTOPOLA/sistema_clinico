<!-- Modal para Registrar Personal -->
<div class="modal fade" id="modalPersonal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Registrar Nuevo Personal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formPersonal" action="api/guardar_personal.php" method="post">
          <input type="hidden" name="action" value="add_personal">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="nombrePersonal" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="nombrePersonal" name="nombre" required>
            </div>
            <div class="col-md-6">
              <label for="apellidosPersonal" class="form-label">Apellidos</label>
              <input type="text" class="form-control" id="apellidosPersonal" name="apellidos" required>
            </div>
            <div class="col-md-6">
              <label for="fechaNacimientoPersonal" class="form-label">Fecha de Nacimiento</label>
              <input type="date" class="form-control" id="fechaNacimientoPersonal" name="fecha_nacimiento">
            </div>
            <div class="col-md-6">
              <label for="telefonoPersonal" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="telefonoPersonal" name="telefono">
            </div>
            <div class="col-md-12">
              <label for="especialidadPersonal" class="form-label">Especialidad</label>
              <input type="text" class="form-control" id="especialidadPersonal" name="especialidad">
            </div>
            <div class="col-md-12">
              <label for="direccionPersonal" class="form-label">Dirección</label>
              <textarea class="form-control" id="direccionPersonal" name="direccion"></textarea>
            </div>
            <div class="col-md-12">
              <label for="correoPersonal" class="form-label">Correo (OPCIONAL)</label>
              <input type="email" class="form-control" id="correoPersonal" name="correo">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formPersonal" class="btn btn-primary rounded-pill"><i class="bi bi-save me-2"></i> guardar Personal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Registrar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i> Registrar Nuevo Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formUsuario" action="api/guardar_usuario.php" method="post">
          <input type="hidden" name="action" value="add_usuario">
          <div class="mb-3">
            <label for="nombreUsuario" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" id="nombreUsuario" name="nombre_usuario" required>
          </div>
          <div class="mb-3">
            <label for="passwordUsuario" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="passwordUsuario" name="contrasena" required>
          </div>
          <div class="mb-3">
            <label for="rolUsuario" class="form-label">Rol</label>
            <select class="form-select" id="rolUsuario" name="id_rol" required>
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
            <label for="personalAsociado" class="form-label">Personal Asociado</label>
            <select class="form-select" id="personalAsociado" name="id_personal" required>
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
        <button type="submit" form="formUsuario" class="btn btn-primary rounded-pill"><i class="bi bi-save me-2"></i> guardar Usuario</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Registrar Paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-hearts me-2"></i> Registrar Nuevo Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formPaciente" action="api/guardar_paciente.php" method="post">
          <input type="hidden" name="action" value="add_paciente">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="nombrePaciente" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="nombrePaciente" name="nombre" required>
            </div>
            <div class="col-md-6">
              <label for="apellidosPaciente" class="form-label">Apellidos</label>
              <input type="text" class="form-control" id="apellidosPaciente" name="apellidos" required>
            </div>
            <div class="col-md-6">
              <label for="fechaNacimientoPaciente" class="form-label">Fecha de Nacimiento</label>
              <input type="date" class="form-control" id="fechaNacimientoPaciente" name="fecha_nacimiento">
            </div>
            <div class="col-md-6">
              <label for="sexoPaciente" class="form-label">Sexo</label>
              <select class="form-select" id="sexoPaciente" name="sexo">
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="telefonoPaciente" class="form-label">Teléfono Titular (OPCIONAL)</label>
              <input type="tel" class="form-control" id="telefonoPaciente" name="telefono">
            </div>
            <div class="col-md-6">
              <label for="emailPaciente" class="form-label">Email (OPCIONAL) </label>
              <input type="email" class="form-control" id="emailPaciente" name="email">
            </div>
             
            <div class="col-md-6">
              <label for="dipPaciente" class="form-label">DIP</label>
              <input type="text" class="form-control" id="dipPaciente" name="dip">
            </div>
            <div class="col-md-6">
              <label for="profesionPaciente" class="form-label">Profesión</label>
              <input type="text" class="form-control" id="profesionPaciente" name="profesion">
            </div>
            <div class="col-md-6">
              <label for="ocupacionPaciente" class="form-label">Ocupación</label>
              <input type="text" class="form-control" id="ocupacionPaciente" name="ocupacion">
            </div>
            <div class="col-md-6">
              <label for="tutorNombrePaciente" class="form-label">Nombre del Tutor</label>
              <input type="text" class="form-control" id="tutorNombrePaciente" name="tutor_nombre">
            </div>
            <div class="col-md-6">
              <label for="telefonoTutorPaciente" class="form-label">Teléfono del Tutor</label>
              <input type="tel" class="form-control" id="telefonoTutorPaciente" name="telefono_tutor">
            </div>
            <div class="col-md-12">
              <label for="direccionPaciente" class="form-label">Dirección</label>
              <textarea class="form-control" id="direccionPaciente" name="direccion"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formPaciente" class="btn btn-primary rounded-pill"><i class="bi bi-save me-2"></i> guardar Paciente</button>
      </div>
    </div>
  </div>
</div>
