#!/bin/bash

# Archivo de salida
OUTPUT="estructura.txt"

# Limpiar archivo anterior si existe
rm -f $OUTPUT

# Encabezado
echo "=======================" >> $OUTPUT
echo " ESTRUCTURA DEL PROYECTO LARAVEL " >> $OUTPUT
echo "=======================" >> $OUTPUT
echo "" >> $OUTPUT

# Listar carpetas y archivos
for DIR in app resources routes; do
  echo ">>> Estructura de: $DIR" >> $OUTPUT
  echo "--------------------------" >> $OUTPUT
  find "$DIR" | sort >> $OUTPUT
  echo "" >> $OUTPUT
done

# Mensaje final
echo "✅ Archivo generado: $OUTPUT"
echo "Puedes verlo con: cat $OUTPUT"
