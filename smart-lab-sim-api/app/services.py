import math
R=8.31446261815324
MOLAR_MASS_CACO3=100.0869
MOLAR_MASS_CO2=44.0095
SIGMA=5.670374419e-8
def simulate_co2(p):
    n_caco3=p.caco3_mass_g/MOLAR_MASS_CACO3
    n_hcl=p.hcl_molarity*(p.hcl_volume_ml/1000)
    theoretical=min(n_caco3,n_hcl/2)
    limiting='Calcium carbonate' if n_caco3 <= n_hcl/2 else 'Hydrochloric acid'
    actual=theoretical*(p.yield_percent/100)
    volume_l=actual*R*(p.temperature_c+273.15)/p.pressure_kpa
    series=[]
    for i in range(21):
        t=p.duration_seconds*i/20
        fraction=1-math.exp(-4*t/p.duration_seconds)
        series.append({'time_s':round(t,2),'co2_volume_l':round(volume_l*fraction,6),'reaction_rate_mol_s':round((actual*4/p.duration_seconds)*math.exp(-4*t/p.duration_seconds),8)})
    warnings=[]
    if p.hcl_molarity>3: warnings.append('Concentrated hydrochloric acid requires enhanced laboratory controls in a physical setting.')
    if p.pressure_kpa<80: warnings.append('Low pressure substantially increases calculated gas volume.')
    return {'simulation_type':'co2','summary':{'limiting_reagent':limiting,'moles_caco3':round(n_caco3,6),'moles_hcl':round(n_hcl,6),'theoretical_moles_co2':round(theoretical,6),'actual_moles_co2':round(actual,6),'co2_volume_l':round(volume_l,6),'co2_mass_g':round(actual*MOLAR_MASS_CO2,6),'yield_percent':round(p.yield_percent,2)},'series':series,'warnings':warnings,'assumptions':['Ideal-gas behaviour','Complete stoichiometric reaction before yield correction','Constant temperature and pressure'],'source':'fastapi-scientific-engine'}
def simulate_heat(p):
    delta=p.hot_temperature_c-p.cold_temperature_c
    if p.mode=='conduction':
        resistance=p.thickness_m/(p.conductivity_w_mk*p.area_m2)
        rate=delta/resistance
        governing='Fourier plane-wall conduction'
    elif p.mode=='convection':
        resistance=1/(p.h_w_m2k*p.area_m2)
        rate=delta/resistance
        governing='Newton law of cooling'
    else:
        hot_k=p.hot_temperature_c+273.15;cold_k=p.cold_temperature_c+273.15
        rate=p.emissivity*SIGMA*p.area_m2*(hot_k**4-cold_k**4)
        resistance=None;governing='Stefan-Boltzmann radiation'
    series=[]
    for i in range(21):
        t=p.duration_seconds*i/20
        series.append({'time_s':round(t,2),'energy_j':round(rate*t,4),'heat_transfer_rate_w':round(rate,4)})
    warnings=[]
    if delta<0:warnings.append('The labelled hot side is cooler; the negative result indicates reversed heat-flow direction.')
    if abs(rate)>1_000_000:warnings.append('The selected parameters produce an unusually large heat-transfer rate; check area, thickness, and units.')
    summary={'mode':p.mode,'governing_relation':governing,'temperature_difference_k':round(delta,4),'heat_transfer_rate_w':round(rate,4),'energy_transferred_j':round(rate*p.duration_seconds,4)}
    if resistance is not None:summary['thermal_resistance_k_w']=round(resistance,8)
    return {'simulation_type':'heat-transfer','summary':summary,'series':series,'warnings':warnings,'assumptions':['Uniform material properties','Constant boundary temperatures','One-dimensional or lumped transfer as applicable'],'source':'fastapi-scientific-engine'}
