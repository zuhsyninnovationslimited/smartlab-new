import axios from "axios";
const api = axios.create({ baseURL: import.meta.env.VITE_API_URL || "/api/v1", headers: { Accept: "application/json" } });
api.interceptors.request.use((config) => { const token=localStorage.getItem("smartlab_token"); if(token) config.headers.Authorization=`Bearer ${token}`; return config; });
api.interceptors.response.use(r=>r, e=>{ if(e.response?.status===401){localStorage.removeItem("smartlab_token");localStorage.removeItem("smartlab_user"); if(!location.pathname.includes("login")) location.href="/login";} return Promise.reject(e); });
export const errorMessage=(e)=>e.response?.data?.message || Object.values(e.response?.data?.errors||{})?.flat()?.[0] || e.message || "Something went wrong.";
export default api;
