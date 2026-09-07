/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useEffect, useState } from "react";
import api from "../services/api";
const AuthContext=createContext(null);
export function AuthProvider({children}){
 const [user,setUser]=useState(()=>JSON.parse(localStorage.getItem("smartlab_user")||"null")); const [loading,setLoading]=useState(Boolean(localStorage.getItem("smartlab_token")));
 useEffect(()=>{if(!localStorage.getItem("smartlab_token")){setLoading(false);return;}api.get("/me").then(r=>{setUser(r.data.user);localStorage.setItem("smartlab_user",JSON.stringify(r.data.user));}).catch(()=>setUser(null)).finally(()=>setLoading(false));},[]);
 const save=(data)=>{localStorage.setItem("smartlab_token",data.token);localStorage.setItem("smartlab_user",JSON.stringify(data.user));setUser(data.user)};
 const login=async(payload)=>{const {data}=await api.post("/login",payload);save(data);return data};
 const register=async(payload)=>{const {data}=await api.post("/register",payload);save(data);return data};
 const logout=async()=>{try{await api.post("/logout")}finally{localStorage.clear();setUser(null)}};
 const value={user,loading,login,register,logout,isInstructor:["instructor","admin"].includes(user?.role),isAdmin:user?.role==="admin"};
 return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
export const useAuth=()=>useContext(AuthContext);
