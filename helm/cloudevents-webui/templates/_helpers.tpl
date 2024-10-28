{{/*
Expand the name of the chart.
*/}}
{{- define "cloudevents-webui.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "cloudevents-webui.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "cloudevents-webui.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "cloudevents-webui.labels" -}}
helm.sh/chart: {{ include "cloudevents-webui.chart" . }}
{{ include "cloudevents-webui.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Common labels PWA
*/}}
{{- define "cloudevents-webui.labelsPWA" -}}
helm.sh/chart: {{ include "cloudevents-webui.chart" . }}
{{ include "cloudevents-webui.selectorLabelsPWA" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "cloudevents-webui.selectorLabels" -}}
app.kubernetes.io/name: {{ include "cloudevents-webui.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
app.kubernetes.io/part-of: {{ include "cloudevents-webui.name" . }}
{{- end }}

{{/*
Selector labels PWA
*/}}
{{- define "cloudevents-webui.selectorLabelsPWA" -}}
app.kubernetes.io/name: {{ include "cloudevents-webui.name" . }}-pwa
app.kubernetes.io/instance: {{ .Release.Name }}
app.kubernetes.io/part-of: {{ include "cloudevents-webui.name" . }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "cloudevents-webui.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "cloudevents-webui.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}
