// services/parcelleService.js

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// ✅ Récupérer toutes les parcelles avec filtres
export async function getParcelles(filters = {}) {
  const params = new URLSearchParams()

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== '') {
      params.append(key, String(value))
    }
  })

  const res = await fetch(`${API_URL}/parcelles?${params.toString()}`)

  if (!res.ok) throw new Error('Erreur lors de la récupération des parcelles')
  return res.json()
}

// ✅ Récupérer une parcelle par ID
export async function getParcelle(id) {
  const res = await fetch(`${API_URL}/parcelles/${id}`)

  if (!res.ok) throw new Error('Parcelle introuvable')
  return res.json()
}

// ✅ Créer une parcelle avec images
export async function createParcelle(formData) {
  const res = await fetch(`${API_URL}/parcelles`, {
    method: 'POST',
    body: formData,
  })

  if (!res.ok) throw new Error('Erreur lors de la création')
  return res.json()
}

// ✅ Mettre à jour une parcelle
export async function updateParcelle(id, formData) {
  const res = await fetch(`${API_URL}/parcelles/${id}`, {
    method: 'POST',
    body: formData,
  })

  if (!res.ok) throw new Error('Erreur lors de la mise à jour')
  return res.json()
}

// ✅ Supprimer une parcelle
export async function deleteParcelle(id) {
  const res = await fetch(`${API_URL}/parcelles/${id}`, {
    method: 'DELETE',
  })

  if (!res.ok) throw new Error('Erreur lors de la suppression')
}

// ✅ Supprimer une image
export async function deleteParcelleImage(imageId) {
  const res = await fetch(`${API_URL}/parcelles/images/${imageId}`, {
    method: 'DELETE',
  })

  if (!res.ok) throw new Error("Erreur lors de la suppression de l'image")
}