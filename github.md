# 📌 Tutorial: Subir y Bajar Cambios con Git y GitHub en VS Code

## 🟢 1. Clonar un Repositorio desde GitHub
Si deseas trabajar con un proyecto existente desde GitHub, debes clonarlo.

```powershell
git clone https://github.com/Andiquis/phplite
```
Esto descargará el repositorio en tu computadora dentro de la carpeta `phplite`.

**Moverse al directorio del proyecto clonado:**
```powershell
cd phplite
```

---

## 🔵 2. Configurar Git (Solo la Primera Vez en tu PC)
Si nunca configuraste Git en tu PC, hazlo con:

```powershell
git config --global user.name "Andiquis"
git config --global user.email "andiquispe942@gmail.com"
```
Puedes verificar tu configuración con:
```powershell
git config --list
```

---

## 🟠 3. Verificar el Estado del Repositorio
Antes de hacer cambios, revisa el estado del repositorio:

```powershell
git status
```
Si ves `working tree clean`, significa que no hay cambios pendientes.

---

## 🟡 4. Subir Cambios al Repositorio
Cada vez que modifiques archivos, sigue estos pasos:

### 1️⃣ **Agregar los archivos al área de preparación:**
```powershell
git add .
```

### 2️⃣ **Confirmar los cambios con un mensaje:**
```powershell
git commit -m "Descripción de los cambios"
```

### 3️⃣ **Subir los cambios a GitHub:**
```powershell
git push origin main
```
Si es la primera vez que subes, puede pedir autenticación. Usa **GitHub CLI** (`gh auth login`) o un **token de acceso personal** en lugar de la contraseña.

---

## 🔴 5. Obtener Cambios desde GitHub
Si alguien más hizo cambios en el repositorio, o si lo actualizaste en otro dispositivo, usa:

```powershell
git pull origin main
```
Esto descargará los cambios y los combinará con tu código local.

---

## 🟣 6. (Opcional) Eliminar la Vinculación de un Repositorio Remoto
Si necesitas cambiar el repositorio remoto, primero elimínalo:

```powershell
git remote remove origin
```
Luego, agrégalo de nuevo con la URL correcta:

```powershell
git remote add origin https://github.com/Andiquis/phplite
```

---

## ✅ Resumen Rápido de los Comandos Más Usados

| Acción | Comando |
|---------|-----------|
| Clonar un repositorio | `git clone URL` |
| Ver estado | `git status` |
| Agregar cambios | `git add .` |
| Confirmar cambios | `git commit -m "Mensaje"` |
| Subir cambios | `git push origin main` |
| Descargar cambios | `git pull origin main` |
| Ver repositorios remotos | `git remote -v` |
| Eliminar un remoto | `git remote remove origin` |
| Agregar un nuevo remoto | `git remote add origin URL` |

---

🔹 **¡Listo! Ahora ya puedes trabajar con Git y GitHub desde VS Code sin problemas.** 🚀
