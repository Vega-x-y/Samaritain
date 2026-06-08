// resources/js/types/parcelles.js

/**
 * @typedef {Object} ParcelleImage
 * @property {number} id
 * @property {number} parcelle_id
 * @property {string} path
 * @property {string} url
 * @property {boolean} principale
 * @property {string} created_at
 */

/**
 * @typedef {Object} Parcelle
 * @property {number} id
 * @property {string} titre
 * @property {string|null} description
 * @property {string} localisation
 * @property {string} quartier
 * @property {string} ville
 * @property {number} superficie
 * @property {number} prix
 * @property {'disponible'|'vendu'|'réservé'} statut
 * @property {string} reference
 * @property {boolean} viabilisee
 * @property {string|null} titre_foncier
 * @property {ParcelleImage[]} images
 * @property {ParcelleImage|null} image_principale
 * @property {string} created_at
 * @property {string} updated_at
 */

/**
 * @typedef {Object} ParcelleFilters
 * @property {string} [ville]
 * @property {string} [quartier]
 * @property {string} [statut]
 * @property {number} [prix_min]
 * @property {number} [prix_max]
 * @property {number} [superficie_min]
 * @property {number} [superficie_max]
 * @property {boolean} [viabilisee]
 * @property {number} [per_page]
 * @property {number} [page]
 */

/**
 * @typedef {Object} ParcellePagination
 * @property {Parcelle[]} data
 * @property {number} current_page
 * @property {number} last_page
 * @property {number} per_page
 * @property {number} total
 */