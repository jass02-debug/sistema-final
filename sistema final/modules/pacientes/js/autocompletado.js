// Este script proporciona funcionalidad de autocompletado para la búsqueda de pacientes.
// Requiere que la variable 'pacientes' esté definida en el contexto PHP antes de ser incluida.

let pacientes = []; // Inicializar como array vacío.  Contiene un array de objetos, cada objeto representando un paciente.

// Obtener referencias a los elementos del DOM.
const pacienteBusqueda = document.getElementById('paciente_busqueda'); // Input de búsqueda del paciente.
const idPacienteInput = document.getElementById('id_paciente'); // Input oculto para el ID del paciente.
const listaPacientes = document.getElementById('lista-pacientes'); // Lista desplegable para mostrar sugerencias.
const form = document.getElementById('searchForm'); // Formulario de búsqueda.


// Evento que se activa cuando el campo de búsqueda obtiene el foco.  Muestra las opciones de autocompletado.
pacienteBusqueda.addEventListener('focus', () => {
  const filtro = pacienteBusqueda.value.toLowerCase(); // Convertir el texto de búsqueda a minúsculas para una comparación insensible a mayúsculas/minúsculas.
  const opcionesFiltradas = pacientes.filter(paciente => paciente.nombre.toLowerCase().includes(filtro) || paciente.cedula.toLowerCase().includes(filtro)).slice(0, 5); // Filtrar los pacientes que coinciden con el filtro y limitar a 5 resultados.
  mostrarOpciones(opcionesFiltradas); // Mostrar las opciones filtradas.
});

// Evento que se activa cada vez que se introduce un carácter en el campo de búsqueda.  Actualiza las opciones de autocompletado.
pacienteBusqueda.addEventListener('input', () => {
  const filtro = pacienteBusqueda.value.toLowerCase(); // Convertir el texto de búsqueda a minúsculas.
  const opcionesFiltradas = pacientes.filter(paciente => paciente.nombre.toLowerCase().includes(filtro) || paciente.cedula.toLowerCase().includes(filtro)).slice(0, 5); // Filtrar y limitar los resultados.
  mostrarOpciones(opcionesFiltradas); // Mostrar las opciones filtradas.
});

// Función para mostrar las opciones de autocompletado en la lista desplegable.
function mostrarOpciones(opciones) {
  listaPacientes.innerHTML = ''; // Limpiar la lista antes de mostrar nuevas opciones.
  opciones.forEach(paciente => { // Iterar sobre cada paciente en las opciones filtradas.
    const elemento = document.createElement('li'); // Crear un elemento de lista para cada paciente.
    elemento.textContent = paciente.nombre + ' (' + paciente.cedula + ')'; // Establecer el texto del elemento de lista con el nombre y la cédula del paciente.
    // Evento que se activa cuando se hace clic en una opción de la lista.  Selecciona el paciente y envía el formulario.
    elemento.addEventListener('click', (event) => {
      event.preventDefault(); // Prevenir el comportamiento por defecto del enlace.
      idPacienteInput.value = paciente.id_paciente; // Establecer el valor del input oculto con el ID del paciente seleccionado.
      pacienteBusqueda.value = paciente.nombre; // Establecer el valor del input de búsqueda con el nombre del paciente seleccionado.
      listaPacientes.innerHTML = ''; // Ocultar la lista desplegable.
      form.submit(); // Enviar el formulario.
    });
    listaPacientes.appendChild(elemento); // Agregar el elemento de lista a la lista desplegable.
  });
}

// Evento que se activa cuando se envía el formulario.  Valida que se haya ingresado un valor en el campo de búsqueda.
form.addEventListener('submit', function(event) {
  if (pacienteBusqueda.value === '') { // Verificar si el campo de búsqueda está vacío.
    event.preventDefault(); // Prevenir el envío del formulario si el campo está vacío.
    alert('Por favor, ingrese un nombre de paciente o cédula.'); // Mostrar una alerta al usuario.
  }
});
