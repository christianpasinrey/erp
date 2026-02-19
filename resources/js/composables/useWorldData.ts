import { ref, type Ref } from 'vue';

export type WorldCountry = { id: number; name: string; iso2: string };
export type WorldState = { id: number; name: string };
export type WorldCity = { id: number; name: string };

const countriesCache = ref<WorldCountry[]>([]);
const statesCache = new Map<string, WorldState[]>();
const citiesCache = new Map<number, WorldCity[]>();

export function useWorldData() {
    const loadingCountries = ref(false);
    const loadingStates = ref(false);
    const loadingCities = ref(false);

    const countries: Ref<WorldCountry[]> = ref([]);
    const states: Ref<WorldState[]> = ref([]);
    const cities: Ref<WorldCity[]> = ref([]);

    async function fetchCountries() {
        if (countriesCache.value.length > 0) {
            countries.value = countriesCache.value;
            return;
        }

        loadingCountries.value = true;
        try {
            const response = await fetch('/api/world/countries');
            const data = await response.json();
            countriesCache.value = data;
            countries.value = data;
        } finally {
            loadingCountries.value = false;
        }
    }

    async function fetchStates(countryCode: string) {
        states.value = [];
        cities.value = [];

        if (!countryCode) return;

        if (statesCache.has(countryCode)) {
            states.value = statesCache.get(countryCode)!;
            return;
        }

        loadingStates.value = true;
        try {
            const response = await fetch(`/api/world/countries/${countryCode}/states`);
            const data = await response.json();
            statesCache.set(countryCode, data);
            states.value = data;
        } finally {
            loadingStates.value = false;
        }
    }

    async function fetchCities(stateId: number) {
        cities.value = [];

        if (!stateId) return;

        if (citiesCache.has(stateId)) {
            cities.value = citiesCache.get(stateId)!;
            return;
        }

        loadingCities.value = true;
        try {
            const response = await fetch(`/api/world/states/${stateId}/cities`);
            const data = await response.json();
            citiesCache.set(stateId, data);
            cities.value = data;
        } finally {
            loadingCities.value = false;
        }
    }

    return {
        countries,
        states,
        cities,
        loadingCountries,
        loadingStates,
        loadingCities,
        fetchCountries,
        fetchStates,
        fetchCities,
    };
}
