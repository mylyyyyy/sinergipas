<div class="bg-white rounded-[40px] border border-slate-200 shadow-xl overflow-hidden card-3d">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[1300px]">
            <thead>
                <tr class="bg-slate-50/80 backdrop-blur-md border-b border-slate-100">
                    <th class="px-8 py-7 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] sticky left-0 bg-slate-50/90 backdrop-blur-md z-20 border-r border-slate-100 shadow-[10px_0_20px_rgba(0,0,0,0.03)]">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                            SHIFT / TANGGAL
                        </div>
                    </th>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php 
                            $isToday = $month->copy()->day($d)->isToday();
                            $isWeekend = $month->copy()->day($d)->isWeekend();
                        @endphp
                        <th class="px-5 py-7 text-center border-r border-slate-100 min-w-[120px] transition-all {{ $isToday ? 'bg-blue-600 text-white' : ($isWeekend ? 'bg-red-50/50' : 'bg-slate-50/50') }}">
                            <div class="relative inline-block">
                                <span class="block text-sm font-black tracking-tight {{ $isToday ? 'text-white' : 'text-slate-900' }}">{{ $d }}</span>
                                <span class="text-[9px] font-black {{ $isToday ? 'text-blue-100' : ($isWeekend ? 'text-red-400' : 'text-slate-400') }} uppercase tracking-widest">{{ $month->copy()->day($d)->translatedFormat('D') }}</span>
                            </div>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($shifts as $shift)
                <tr class="group hover:bg-slate-50/30 transition-all duration-300">
                    <td class="px-8 py-6 sticky left-0 bg-white group-hover:bg-slate-50/90 backdrop-blur-sm z-10 border-r border-slate-100 shadow-[10px_0_20px_rgba(0,0,0,0.02)]">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center 
                                {{ str_contains(strtoupper($shift->name), 'PAGI') ? 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-100' : '' }}
                                {{ str_contains(strtoupper($shift->name), 'SIANG') ? 'bg-gradient-to-br from-blue-400 to-indigo-600 text-white shadow-lg shadow-blue-100' : '' }}
                                {{ str_contains(strtoupper($shift->name), 'MALAM') ? 'bg-gradient-to-br from-slate-800 to-slate-950 text-white shadow-lg shadow-slate-300' : '' }}
                                transition-all duration-500 group-hover:rotate-6 group-hover:scale-110">
                                <i data-lucide="{{ str_contains(strtoupper($shift->name), 'PAGI') ? 'sun' : (str_contains(strtoupper($shift->name), 'SIANG') ? 'cloud-sun' : 'moon') }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-black text-slate-900 uppercase tracking-widest block">{{ $shift->name }}</span>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">{{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}</p>
                                </div>
                            </div>
                        </div>
                    </td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php 
                            $dateStr = $month->copy()->day($d)->format('Y-m-d');
                            $daySchedules = $currentSchedules->get($dateStr . '_' . $shift->id) ?? collect();
                            $isToday = $month->copy()->day($d)->isToday();
                            $isWeekend = $month->copy()->day($d)->isWeekend();
                            $hasSquad = $daySchedules->count() > 0;
                        @endphp
                        <td class="p-3 border-r border-slate-50 text-center transition-all duration-300 {{ $isToday ? 'bg-blue-50/10' : ($isWeekend ? 'bg-red-50/5' : '') }}">
                            <div class="relative group/cell px-1">
                                <select 
                                    onchange="updateSchedule('{{ $dateStr }}', {{ $shift->id }}, this.value, '{{ $type }}')"
                                    class="w-full pl-2 pr-8 py-3.5 rounded-2xl border-2 border-transparent bg-slate-50/50 text-xs font-black text-center appearance-none cursor-pointer hover:bg-white hover:border-blue-200 hover:shadow-md focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all duration-300 bg-[url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%2394a3b8%27 stroke-width=%273%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e')] bg-[length:1.2em] bg-[position:right_0.75rem_center] bg-no-repeat
                                    {{ $hasSquad ? ($type === 'p2u' ? 'text-indigo-600 bg-indigo-50/50 border-indigo-100 shadow-sm' : 'text-blue-600 bg-blue-50/50 border-blue-100 shadow-sm') : 'text-slate-400' }}">
                                    <option value="">- KOSONG -</option>
                                    @foreach($squads as $squad)
                                        <option value="{{ $squad->id }}" {{ $daySchedules->contains('squad_id', $squad->id) ? 'selected' : '' }}>
                                            @if(!str_contains(strtoupper($squad->name), 'REGU')) 
                                                REGU {{ strtoupper($squad->name) }}
                                            @else
                                                {{ strtoupper($squad->name) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                
                                @if($hasSquad)
                                    <div class="absolute -top-1 -right-1 flex gap-0.5">
                                        <div class="w-2.5 h-2.5 {{ $type === 'p2u' ? 'bg-indigo-500' : 'bg-blue-500' }} rounded-full border-2 border-white shadow-sm animate-bounce"></div>
                                    </div>
                                @endif

                                <div class="absolute inset-0 border-2 border-blue-500/0 rounded-2xl pointer-events-none group-hover/cell:border-blue-500/10 transition-all duration-500"></div>
                            </div>
                        </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


