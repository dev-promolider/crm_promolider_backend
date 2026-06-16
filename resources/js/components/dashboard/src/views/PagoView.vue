<template>
  <div class="pago-container">
    <!-- Top Header -->
    <div class="top-header">
      <h2 class="top-header-title">Crear nueva cuenta?</h2>
      <span class="top-header-referred">Referido por: <strong>{{ sponsorName || 'Promolider' }}</strong></span>
    </div>

    <!-- Progress Steps -->
    <div class="steps-bar">
      <div
        v-for="(step, index) in steps"
        :key="index"
        class="step-item"
        :class="{ 'step-active': currentStep === index, 'step-completed': currentStep > index }"
        @click="goToStep(index)"
      >
        <div class="step-circle">{{ currentStep > index ? '✓' : index + 1 }}</div>
        <span class="step-label">{{ step }}</span>
      </div>
    </div>

    <!-- Step 1: Cuenta -->
    <div v-if="currentStep === 0" class="step-content">
      <h2 class="step-title">Crear tu cuenta</h2>
      <div class="form-grid">          <!-- Usuario -->
        <div class="form-group">
          <label>Usuario</label>
          <input
            v-model="form.usuario"
            type="text"
            placeholder="Nombre de usuario"
            maxlength="50"
            :class="{ 'input-error': touched.usuario && errors.usuario }"
            @blur="touch('usuario'); validateField('usuario'); checkDuplicate('usuario')"
            @input="validateField('usuario')"
          />
          <span v-if="touched.usuario && errors.usuario" class="field-error">{{ errors.usuario }}</span>
          <span v-else-if="touched.usuario && checking.usuario" class="field-hint">Verificando disponibilidad...</span>
          <span v-else class="field-hint">Solo letras, números y guion bajo (_). Mínimo 4 caracteres.</span>
        </div>

        <!-- Correo (editable) -->
        <div class="form-group">
          <label>Correo</label>
          <input
            v-model="form.correo"
            type="email"
            placeholder="Correo electrónico"
            :class="{ 'input-error': touched.correo && errors.correo }"
            @blur="touch('correo'); validateField('correo'); checkDuplicate('correo')"
            @input="validateField('correo')"
          />
          <span v-if="touched.correo && errors.correo" class="field-error">{{ errors.correo }}</span>
          <span v-else-if="touched.correo && checking.correo" class="field-hint">Verificando correo...</span>
          <span v-else class="field-hint">Recibirás notificaciones importantes en este correo.</span>
        </div>

        <!-- Contraseña -->
        <div class="form-group">
          <label>Contraseña</label>
          <div class="password-wrapper">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Contraseña"
              :class="{ 'input-error': touched.password && errors.password }"
              @blur="touch('password')"
              @input="validateField('password'); validateField('password_confirm')"
            />
            <button type="button" class="toggle-password" @click="showPassword = !showPassword">
              {{ showPassword ? '👁' : '👁' }}
            </button>
          </div>
          <!-- Barra de fortaleza -->
          <div v-if="form.password" class="password-strength">
            <div class="strength-bar">
              <div class="strength-fill" :style="{ width: passwordStrength.percent + '%' }" :class="passwordStrength.cls"></div>
            </div>
            <span class="strength-label" :class="passwordStrength.cls">{{ passwordStrength.label }}</span>
          </div>
          <span v-if="touched.password && errors.password" class="field-error">{{ errors.password }}</span>
          <span v-else class="field-hint">Mínimo 8 caracteres. Combina mayúsculas, minúsculas y números.</span>
        </div>

        <!-- Confirmar Contraseña -->
        <div class="form-group">
          <label>Confirmar Contraseña</label>
          <div class="password-wrapper">
            <input
              v-model="form.password_confirm"
              :type="showPasswordConfirm ? 'text' : 'password'"
              placeholder="Confirmar contraseña"
              :class="{ 'input-error': touched.password_confirm && errors.password_confirm }"
              @blur="touch('password_confirm'); validateField('password_confirm')"
              @input="validateField('password_confirm')"
            />
            <button type="button" class="toggle-password" @click="showPasswordConfirm = !showPasswordConfirm">
              {{ showPasswordConfirm ? '🙈' : '👁' }}
            </button>
          </div>
          <span v-if="touched.password_confirm && errors.password_confirm" class="field-error">{{ errors.password_confirm }}</span>
          <span v-else class="field-hint">Debe ser idéntica a la contraseña ingresada arriba.</span>
        </div>

        <!-- Tipo de Usuario -->
        <div class="form-group">
          <label>Tipo de Usuario</label>
          <select
            v-model="form.tipo_usuario"
            :class="{ 'input-error': touched.tipo_usuario && errors.tipo_usuario }"
            @change="touch('tipo_usuario'); validateField('tipo_usuario')"
            @blur="touch('tipo_usuario')"
          >
            <option value="">Seleccionar...</option>
            <option value="distribuidor">Distribuidor</option>
          </select>
          <span v-if="touched.tipo_usuario && errors.tipo_usuario" class="field-error">{{ errors.tipo_usuario }}</span>
          <span v-else class="field-hint">Selecciona el rol con el que operarás en la plataforma.</span>
        </div>

      </div>
      <div class="btn-row">
        <button class="btn-next" @click="goToStep(1)">Siguiente →</button>
      </div>
    </div>

    <!-- Step 2: Perfil -->
    <div v-if="currentStep === 1" class="step-content">
      <h2 class="step-title">Completa tu perfil</h2>
      <div class="form-grid">

        <!-- Nombre -->
        <div class="form-group">
          <label>Nombre</label>
          <input
            v-model="form.nombre"
            type="text"
            placeholder="Nombre"
            :class="{ 'input-error': touched.nombre && errors.nombre }"
            @blur="touch('nombre'); validateField('nombre')"
            @input="validateField('nombre')"
          />
          <span v-if="touched.nombre && errors.nombre" class="field-error">{{ errors.nombre }}</span>
          <span v-else class="field-hint">Solo letras, tal como aparece en tu documento.</span>
        </div>

        <!-- Apellido -->
        <div class="form-group">
          <label>Apellido</label>
          <input
            v-model="form.apellido"
            type="text"
            placeholder="Apellido"
            :class="{ 'input-error': touched.apellido && errors.apellido }"
            @blur="touch('apellido'); validateField('apellido')"
            @input="validateField('apellido')"
          />
          <span v-if="touched.apellido && errors.apellido" class="field-error">{{ errors.apellido }}</span>
          <span v-else class="field-hint">Solo letras, tal como aparece en tu documento.</span>
        </div>

        <!-- Teléfono -->
        <div class="form-group">
          <label>Teléfono</label>
          <input
            v-model="form.telefono"
            type="tel"
            placeholder="Teléfono"
            maxlength="15"
            :class="{ 'input-error': touched.telefono && errors.telefono }"
            @blur="touch('telefono'); validateField('telefono')"
            @input="validateField('telefono')"
          />
          <span v-if="touched.telefono && errors.telefono" class="field-error">{{ errors.telefono }}</span>
          <span v-else class="field-hint">Solo dígitos, sin espacios ni guiones. Ej: 987654321</span>
        </div>

        <!-- Fecha de Nacimiento -->
        <div class="form-group">
          <label>Fecha de Nacimiento</label>
          <input
            v-model="form.fecha_nacimiento"
            type="date"
            :max="maxBirthDate"
            :class="{ 'input-error': touched.fecha_nacimiento && errors.fecha_nacimiento }"
            @blur="touch('fecha_nacimiento'); validateField('fecha_nacimiento')"
            @change="touch('fecha_nacimiento'); validateField('fecha_nacimiento')"
          />
          <span v-if="touched.fecha_nacimiento && errors.fecha_nacimiento" class="field-error">{{ errors.fecha_nacimiento }}</span>
          <span v-else class="field-hint">Debes tener al menos 18 años para registrarte.</span>
        </div>

        <!-- Tipo de Documento -->
        <div class="form-group">
          <label>Tipo de Documento</label>
          <select
            v-model="form.tipo_documento"
            :class="{ 'input-error': touched.tipo_documento && errors.tipo_documento }"
            @change="touch('tipo_documento'); validateField('tipo_documento'); validateField('numero_documento')"
            @blur="touch('tipo_documento')"
          >
            <option value="">Seleccionar...</option>
            <option value="dni">DNI</option>
            <option value="carnet_extranjeria">Carnet de Extranjería</option>
            <option value="pasaporte">Pasaporte</option>
            <option value="otro">Otro</option>
          </select>
          <span v-if="touched.tipo_documento && errors.tipo_documento" class="field-error">{{ errors.tipo_documento }}</span>
          <span v-else class="field-hint">Selecciona el tipo de documento que presentarás.</span>
        </div>

        <!-- Número de Documento -->
        <div class="form-group">
          <label>Número de Documento</label>
          <input
            v-model="form.numero_documento"
            type="text"
            :placeholder="documentPlaceholder"
            :maxlength="documentMaxLength"
            :class="{ 'input-error': touched.numero_documento && errors.numero_documento }"
            @blur="touch('numero_documento'); validateField('numero_documento'); checkDuplicate('numero_documento')"
            @input="validateField('numero_documento')"
          />
          <span v-if="touched.numero_documento && errors.numero_documento" class="field-error">{{ errors.numero_documento }}</span>
          <span v-else-if="touched.numero_documento && checking.numero_documento" class="field-hint">Verificando documento...</span>
          <span v-else class="field-hint">{{ documentHint }}</span>
        </div>

        <!-- País -->
        <div class="form-group">
          <label>País</label>
          <input v-model="form.pais" type="text" placeholder="País" readonly />
        </div>

      </div>
      <div class="btn-row">
        <button class="btn-back" @click="goToStep(0)">← Atrás</button>
        <button class="btn-next" @click="goToStep(2)">Siguiente →</button>
      </div>
    </div>

    <!-- Step 3: Membresia -->
    <div v-if="currentStep === 2" class="step-content">
      <div class="form-grid">
        <div class="form-group">
          <label>Tipo de cuenta</label>
          <select v-model="form.tipo_cuenta">
            <option value="pre_registro">Pre registro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Precio</label>
          <input type="text" value="$ 45.00" readonly />
        </div>
        <div class="form-group">
          <label>IVA</label>
          <input type="text" value="18%" readonly />
        </div>
        <div class="form-group">
          <label>Costo total de la membresía</label>
          <input type="text" value="$ 53.10" readonly />
        </div>
        <div class="form-group" style="grid-column: 1 / -1; max-width: 50%;">
          <label>Método de Pago</label>
          <select v-model="form.metodo_pago" class="select-green-border">
            <option value="tarjeta">Tarjeta crédito / débito</option>
          </select>
        </div>
      </div>
      <div class="btn-row">
        <button class="btn-back-text" @click="goToStep(1)">&#60; Atras</button>
        <button class="btn-submit" :disabled="isPaying" @click="handleSubmit">
          {{ isPaying ? 'Procesando...' : 'Pagar' }}
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>
          </svg>
        </button>
      </div>
      <p v-if="paymentError" class="payment-error">{{ paymentError }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const currentStep = ref(0)
const steps = ['Cuenta', 'Perfil', 'Membresia']

const isPaying = ref(false)
const paymentError = ref('')
const sponsorName = ref('')

const errors = ref<Record<string, string>>({})
const touched = ref<Record<string, boolean>>({})
const checking = ref<Record<string, boolean>>({})

const form = ref({
  usuario: '',
  correo: '',
  password: '',
  password_confirm: '',
  tipo_usuario: '',
  nombre: '',
  apellido: '',
  telefono: '',
  fecha_nacimiento: '',
  tipo_documento: '',
  numero_documento: '',
  pais: '',
  metodo_pago: 'tarjeta',
  tipo_cuenta: 'pre_registro',
  referidor: '',
  lado: '',
  preregistro_id: '',
  reuse_pending_registration: false,
})

// ─── Max birth date (18 años atrás) ─────────────────────────────────────────
const maxBirthDate = computed(() => {
  const d = new Date()
  d.setFullYear(d.getFullYear() - 18)
  return d.toISOString().split('T')[0]
})

// ─── Placeholder, maxlength y hint dinámicos según tipo de documento ─────────
const documentPlaceholder = computed(() => {
  switch (form.value.tipo_documento) {
    case 'dni': return 'Ej: 12345678'
    case 'carnet_extranjeria': return 'Ej: 000123456'
    case 'pasaporte': return 'Ej: AB123456'
    default: return 'Número de documento'
  }
})

const documentMaxLength = computed(() => {
  switch (form.value.tipo_documento) {
    case 'dni': return 8
    case 'pasaporte': return 20
    default: return 20
  }
})

const documentHint = computed(() => {
  switch (form.value.tipo_documento) {
    case 'dni': return 'El DNI debe tener exactamente 8 dígitos numéricos.'
    case 'carnet_extranjeria': return 'Entre 6 y 12 caracteres alfanuméricos.'
    case 'pasaporte': return 'Entre 6 y 20 caracteres alfanuméricos.'
    default: return 'Ingresa el número tal como aparece en tu documento.'
  }
})

// ─── Fortaleza de contraseña ─────────────────────────────────────────────────
const passwordStrength = computed(() => {
  const p = form.value.password
  if (!p) return { percent: 0, label: '', cls: '' }
  let score = 0
  if (p.length >= 8) score++
  if (p.length >= 10) score++
  if (/[A-Z]/.test(p)) score++
  if (/[a-z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  if (score <= 2) return { percent: 33, label: 'Débil', cls: 'strength-weak' }
  if (score <= 4) return { percent: 66, label: 'Media', cls: 'strength-medium' }
  return { percent: 100, label: 'Fuerte', cls: 'strength-strong' }
})

// ─── Touch ───────────────────────────────────────────────────────────────────
function touch(field: string) {
  touched.value[field] = true
}

// ─── Validación campo a campo ─────────────────────────────────────────────────
function validateField(field: string) {
  const f = form.value

  switch (field) {
    case 'usuario':
      if (!f.usuario.trim())
        errors.value.usuario = 'Ingresa un nombre de usuario.'
      else if (f.usuario.length < 4)
        errors.value.usuario = 'Mínimo 4 caracteres.'
      else if (f.usuario.length > 50)
        errors.value.usuario = 'Máximo 50 caracteres.'
      else if (!/^[A-Za-z0-9_]+$/.test(f.usuario))
        errors.value.usuario = 'Solo letras, números y guion bajo (_). Sin espacios.'
      else
        delete errors.value.usuario
      break

    case 'correo':
      if (!f.correo.trim())
        errors.value.correo = 'Ingresa tu correo electrónico.'
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.correo))
        errors.value.correo = 'El correo no tiene un formato válido. Ej: nombre@dominio.com'
      else
        delete errors.value.correo
      break

    case 'password':
      if (!f.password)
        errors.value.password = 'Ingresa una contraseña.'
      else if (f.password.length < 8)
        errors.value.password = 'La contraseña debe tener al menos 8 caracteres.'
      else if (!/[A-Z]/.test(f.password))
        errors.value.password = 'Agrega al menos una letra mayúscula (A-Z).'
      else if (!/[a-z]/.test(f.password))
        errors.value.password = 'Agrega al menos una letra minúscula (a-z).'
      else if (!/[0-9]/.test(f.password))
        errors.value.password = 'Agrega al menos un número (0-9).'
      else
        delete errors.value.password
      break

    case 'password_confirm':
      if (!f.password_confirm)
        errors.value.password_confirm = 'Confirma tu contraseña.'
      else if (f.password !== f.password_confirm)
        errors.value.password_confirm = 'Las contraseñas no coinciden. Verifica que sean iguales.'
      else
        delete errors.value.password_confirm
      break

    case 'tipo_usuario':
      if (!f.tipo_usuario)
        errors.value.tipo_usuario = 'Selecciona un tipo de usuario.'
      else
        delete errors.value.tipo_usuario
      break

    case 'nombre':
      if (!f.nombre.trim())
        errors.value.nombre = 'Ingresa tu nombre.'
      else if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'\-]+$/.test(f.nombre))
        errors.value.nombre = 'El nombre no puede contener números ni símbolos.'
      else
        delete errors.value.nombre
      break

    case 'apellido':
      if (!f.apellido.trim())
        errors.value.apellido = 'Ingresa tu apellido.'
      else if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'\-]+$/.test(f.apellido))
        errors.value.apellido = 'El apellido no puede contener números ni símbolos.'
      else
        delete errors.value.apellido
      break

    case 'telefono':
      if (!f.telefono.trim())
        errors.value.telefono = 'Ingresa tu número de teléfono.'
      else if (!/^[0-9]{7,15}$/.test(f.telefono.replace(/\s/g, '')))
        errors.value.telefono = 'Ingresa solo dígitos, entre 7 y 15 números, sin espacios.'
      else
        delete errors.value.telefono
      break

    case 'fecha_nacimiento': {
      if (!f.fecha_nacimiento) {
        errors.value.fecha_nacimiento = 'Selecciona tu fecha de nacimiento.'
      } else {
        const dob = new Date(f.fecha_nacimiento)
        const today = new Date()
        let age = today.getFullYear() - dob.getFullYear()
        const m = today.getMonth() - dob.getMonth()
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--
        if (age < 18)
          errors.value.fecha_nacimiento = `Debes tener al menos 18 años para registrarte (actualmente tendrías ${age}).`
        else
          delete errors.value.fecha_nacimiento
      }
      break
    }

    case 'tipo_documento':
      if (!f.tipo_documento)
        errors.value.tipo_documento = 'Selecciona un tipo de documento.'
      else
        delete errors.value.tipo_documento
      break

    case 'numero_documento':
      if (!f.numero_documento.trim())
        errors.value.numero_documento = 'Ingresa tu número de documento.'
      else if (f.tipo_documento === 'dni' && !/^[0-9]{8}$/.test(f.numero_documento))
        errors.value.numero_documento = 'El DNI debe tener exactamente 8 dígitos numéricos.'
      else if (f.tipo_documento === 'carnet_extranjeria' && !/^[A-Za-z0-9]{6,12}$/.test(f.numero_documento))
        errors.value.numero_documento = 'El carnet debe tener entre 6 y 12 caracteres alfanuméricos.'
      else if (f.tipo_documento === 'pasaporte' && !/^[A-Za-z0-9]{6,20}$/.test(f.numero_documento))
        errors.value.numero_documento = 'El pasaporte debe tener entre 6 y 20 caracteres alfanuméricos.'
      else
        delete errors.value.numero_documento
      break
  }
}

// ─── Verificación de duplicados (usuario / correo / documento) ───────────────
const debounceTimers: Record<string, ReturnType<typeof setTimeout>> = {}

async function checkDuplicate(field: string) {
  if (errors.value[field]) return // No verificar si ya hay error local

  const valueMap: Record<string, string> = {
    usuario: form.value.usuario,
    correo: form.value.correo,
    numero_documento: form.value.numero_documento,
  }
  const value = valueMap[field]
  if (!value?.trim()) return

  clearTimeout(debounceTimers[field])
  debounceTimers[field] = setTimeout(async () => {
    checking.value[field] = true
    try {
      const params = new URLSearchParams({ field, value })
      const res = await fetch(`/preregistro/check-duplicate?${params}`, {
        headers: { 'Accept': 'application/json' },
      })
      const data = await res.json()
      if (data.taken) {
        errors.value[field] = data.message
      }
    } catch {
      // Silencioso — la validación final del servidor lo capturará
    } finally {
      checking.value[field] = false
    }
  }, 400)
}

// ─── Validar step completo y marcar todos los campos ─────────────────────────
function touchAndValidateStep(step: number): boolean {
  const stepFields: Record<number, string[]> = {
    0: ['usuario', 'correo', 'password', 'password_confirm', 'tipo_usuario'],
    1: ['nombre', 'apellido', 'telefono', 'fecha_nacimiento', 'tipo_documento', 'numero_documento'],
    2: [],
  }
  for (const f of stepFields[step] ?? []) {
    touched.value[f] = true
    validateField(f)
  }
  return (stepFields[step] ?? []).every(f => !errors.value[f])
}

function goToStep(step: number) {
  paymentError.value = ''
  if (step > currentStep.value) {
    const valid = touchAndValidateStep(currentStep.value)
    if (!valid) return
  }
  currentStep.value = step
}

// ─── Submit ──────────────────────────────────────────────────────────────────
async function handleSubmit() {
  paymentError.value = ''

  for (let s = 0; s <= 1; s++) {
    const ok = touchAndValidateStep(s)
    if (!ok) {
      currentStep.value = s
      return
    }
  }

  if (!form.value.referidor) {
    paymentError.value = 'No se encontró el usuario que te invitó. Vuelve al enlace original.'
    return
  }
  if (!form.value.lado) {
    paymentError.value = 'No se detectó el lado de tu registro. Vuelve al enlace original.'
    return
  }

  isPaying.value = true

  try {
    const payload = { ...form.value, preregistro_id: form.value.preregistro_id || null }

    const response = await fetch('/preregistro/openpay', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (response.status === 422) {
      if (data.errors) {
        const step0Fields = ['usuario', 'correo', 'password', 'password_confirm', 'tipo_usuario']
        let firstErrorStep = 2
        Object.entries(data.errors).forEach(([key, value]) => {
          errors.value[key] = Array.isArray(value) ? (value[0] as string) : String(value)
          touched.value[key] = true
          if (step0Fields.includes(key) && firstErrorStep > 0) firstErrorStep = 0
          else if (!step0Fields.includes(key) && firstErrorStep > 1) firstErrorStep = 1
        })
        currentStep.value = firstErrorStep
      }
      throw new Error(data.message || 'Algunos datos no son válidos. Revisa los campos marcados.')
    }

    if (!response.ok || !data.payment_url) {
      throw new Error(data.message || 'No se pudo iniciar el pago.')
    }

    window.location.href = data.payment_url

  } catch (error) {
    paymentError.value = error instanceof Error ? error.message : 'Ocurrió un error inesperado.'
  } finally {
    isPaying.value = false
  }
}

// ─── onMounted ───────────────────────────────────────────────────────────────
onMounted(() => {
  form.value.usuario        = sessionStorage.getItem('preregistro_usuario') || ''
  form.value.correo         = sessionStorage.getItem('preregistro_correo') || ''
  form.value.tipo_usuario   = sessionStorage.getItem('preregistro_tipo_usuario') || ''
  form.value.nombre         = sessionStorage.getItem('preregistro_nombre') || ''
  form.value.apellido       = sessionStorage.getItem('preregistro_apellido') || ''
  form.value.telefono       = sessionStorage.getItem('preregistro_whatsapp') || ''
  form.value.fecha_nacimiento = sessionStorage.getItem('preregistro_fecha_nacimiento') || ''
  form.value.tipo_documento = sessionStorage.getItem('preregistro_tipo_documento') || ''
  form.value.numero_documento = sessionStorage.getItem('preregistro_numero_documento') || ''
  form.value.pais           = sessionStorage.getItem('preregistro_pais') || ''
  form.value.tipo_cuenta    = sessionStorage.getItem('preregistro_tipo_cuenta') || 'pre_registro'
  form.value.metodo_pago    = sessionStorage.getItem('preregistro_metodo_pago') || 'tarjeta'
  form.value.referidor      = sessionStorage.getItem('preregistro_referidor') || ''
  form.value.lado           = sessionStorage.getItem('preregistro_lado') || ''
  form.value.preregistro_id = sessionStorage.getItem('preregistro_id') || ''
  form.value.reuse_pending_registration = sessionStorage.getItem('preregistro_pending_complete') === '1'
  sponsorName.value         = form.value.referidor
  // reuse_pending_registration: los datos se cargan del sessionStorage
  // (prefijados por el backend). Solo necesita establecer su contraseña.

  if (!form.value.pais) {
    fetch('https://ipapi.co/json/')
    .then(res => res.json())
    .then(data => { if (data.country_name) form.value.pais = data.country_name })
    .catch(() => { form.value.pais = 'Perú' })
  }
})
</script>

<style scoped>
.pago-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 32px 24px;
  font-family: 'Nunito', sans-serif;
}

/* Top Header */
.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.top-header-title {
  font-size: 22px;
  color: #4b5563;
  font-weight: 700;
}

.top-header-referred {
  font-size: 15px;
  color: #6b7280;
}

.top-header-referred strong {
  color: #374151;
  font-weight: 700;
}

/* Steps Bar */
.steps-bar {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 40px;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  flex: 1;
  max-width: 140px;
}

.step-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #e5e7eb;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.2s;
}

.step-active .step-circle {
  background: #18d600;
  color: #fff;
  box-shadow: 0 2px 8px rgba(24, 214, 0, 0.35);
}

.step-completed .step-circle {
  background: #18d600;
  color: #fff;
}

.step-label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-align: center;
}

.step-active .step-label {
  color: #18d600;
}

/* Step Content */
.step-content {
  background: #fff;
  border-radius: 14px;
  padding: 32px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
}

.step-title {
  font-family: 'Exo 2', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
}

/* Form Grid */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-group input,
.form-group select {
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  color: #111827;
  background: #fff;
  transition: border-color 0.2s;
  outline: none;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #18d600;
}

.form-group input[readonly] {
  background: #f3f4f6;
  color: #6b7280;
}

/* Error state */
.input-error {
  border-color: #dc2626 !important;
  background: #fff5f5 !important;
}

/* Messages */
.field-error {
  font-size: 12px;
  color: #dc2626;
  margin-top: 2px;
  font-weight: 600;
}

.field-hint {
  font-size: 11px;
  color: #9ca3af;
  margin-top: 2px;
}

/* Password wrapper */
.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-wrapper input {
  width: 100%;
  padding-right: 45px;
}

.toggle-password {
  position: absolute;
  right: 12px;
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 16px;
  color: #6b7280;
}

.toggle-password:hover {
  color: #18d600;
}

/* Password strength */
.password-strength {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 5px;
}

.strength-bar {
  flex: 1;
  height: 4px;
  background: #e5e7eb;
  border-radius: 99px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.3s, background 0.3s;
}

.strength-weak   { background: #dc2626; color: #dc2626; }
.strength-medium { background: #f59e0b; color: #f59e0b; }
.strength-strong { background: #16a34a; color: #16a34a; }

.strength-label {
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

/* Buttons */
.btn-row {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.btn-back {
  padding: 10px 24px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: #18d600;
  color: #18d600;
}

.btn-next {
  padding: 10px 24px;
  border: none;
  border-radius: 8px;
  background: #18d600;
  color: #fff;
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-next:hover {
  background: #14b800;
  transform: translateY(-1px);
}

.btn-submit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 32px;
  border: none;
  border-radius: 8px;
  background: #18d600;
  color: #fff;
  font-family: 'Exo 2', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-submit:hover {
  background: #14b800;
  transform: translateY(-2px);
}

.btn-submit:disabled {
  cursor: wait;
  opacity: 0.75;
  transform: none;
}

.payment-error {
  margin-top: 16px;
  color: #dc2626;
  font-size: 14px;
  font-weight: 700;
  text-align: right;
}

.btn-back-text {
  padding: 10px 16px;
  background: transparent;
  border: none;
  font-family: 'Nunito', sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back-text:hover {
  color: #18d600;
}

.select-green-border {
  border-color: #18d600 !important;
  color: #6b7280 !important;
}

.select-green-border:focus {
  box-shadow: 0 0 0 3px rgba(24, 214, 0, 0.2);
}

/* Responsive */
@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .step-content {
    padding: 20px;
  }

  .steps-bar {
    gap: 4px;
  }

  .step-circle {
    width: 30px;
    height: 30px;
    font-size: 12px;
  }

  .step-label {
    font-size: 10px;
  }
}
</style>
