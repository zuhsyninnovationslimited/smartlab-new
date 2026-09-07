import { FlaskConical } from "lucide-react";
export default function Logo({compact=false}){return <div className="logo"><span className="logo-mark"><FlaskConical size={22}/></span>{!compact&&<span><b>Smart</b>Lab<small>Virtual Science Platform</small></span>}</div>}
