import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

// Helper: normalize a date-like value to 'YYYY-MM-DD' without timezone
function normalizeDateStr(val) {
    const s = String(val ?? '').trim();
    if (!s) return '';
    // For ISO strings like 'YYYY-MM-DDTHH:mm:ssZ' or objects stringified
    if (s.length >= 10) return s.slice(0, 10);
    return s;
}

// Helper: normalize a time-like value to 'HH:MM:SS' without timezone
function normalizeTimeStr(val) {
    let s = String(val ?? '').trim();
    if (!s) return '00:00:00';

    // If an ISO datetime/time slipped in
    if (s.includes('T')) s = s.split('T').pop();
    if (s.includes(' ')) s = s.split(' ').pop();

    // Strip trailing timezone or Z and microseconds
    s = s.replace(/Z$/, '')
         .replace(/[+-]\d{2}:?\d{2}$/, '')
         .replace(/\.[0-9]+$/, '');

    // Ensure HH:MM:SS
    if (/^\d{2}:\d{2}$/.test(s)) return s + ':00';
    if (/^\d{2}:\d{2}:\d{2}/.test(s)) return s.slice(0, 8);

    // Fallback: extract first time-like token
    const m = s.match(/(\d{2}:\d{2}(?::\d{2})?)/);
    if (m) {
        let t = m[1];
        if (/^\d{2}:\d{2}$/.test(t)) t += ':00';
        return t;
    }
    return '00:00:00';
}

window.initStaffCalendar = function(schedules) {
    console.log('=== INSIDE initStaffCalendar ===');
    console.log('Received schedules:', schedules);
    
    const calendarEl = document.getElementById('fullcalendar');
    console.log('Calendar element found:', !!calendarEl);
    
    if (!calendarEl) {
        console.error('fullcalendar element not found!');
        return;
    }

    // Convert schedules to FullCalendar events
    const events = schedules.map(schedule => {
        // Normalize date/time to avoid timezone-induced off-by-one shifts
        const dateOnly = normalizeDateStr(schedule.schedule_date);
        const startTimeOnly = normalizeTimeStr(schedule.start_time);
        const endTimeOnly = schedule.end_time ? normalizeTimeStr(schedule.end_time) : null;
        
        const event = {
            id: schedule.schedule_id,
            title: schedule.title,
            start: `${dateOnly}T${startTimeOnly}`,
            end: endTimeOnly ? `${dateOnly}T${endTimeOnly}` : null,
            description: schedule.description,
            location: schedule.location,
            eventType: schedule.event_type,
            isPublic: schedule.is_public,
            backgroundColor: getEventColor(schedule.event_type),
            borderColor: getEventColor(schedule.event_type),
            extendedProps: {
                description: schedule.description,
                location: schedule.location,
                eventType: schedule.event_type,
                isPublic: schedule.is_public,
                scheduleData: schedule
            }
        };
        console.log('Created event:', event);
        return event;
    });
    
    console.log('Total events created:', events.length);
    console.log('Events array:', events);

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: '',
            right: 'title' // Move title to the right side
        },
        events: events,
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: false, // Show all events in the cell
        weekends: true,
        eventDisplay: 'block', // Display events as blocks
        displayEventTime: true, // Show time in events
        displayEventEnd: true, // Show end time
        eventTimeFormat: { // Format time display
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        nextDayThreshold: '00:00:00', // Treat events as same-day
        fixedWeekCount: false, // Don't force 6 weeks to be shown
        showNonCurrentDates: false, // Hide dates from other months
        
        // Force alignment when each event mounts
        eventDidMount: function(info) {
            const harness = info.el.closest('.fc-daygrid-event-harness');
            if (harness) {
                harness.style.setProperty('top', '0px', 'important');
                harness.style.setProperty('position', 'relative', 'important');
                harness.style.setProperty('inset', 'auto', 'important');
                harness.style.setProperty('left', 'auto', 'important');
                harness.style.setProperty('right', 'auto', 'important');
            }
        },
        
        // Click on date to add event
        dateClick: function(info) {
            openAddModal(info.dateStr);
        },
        
        // Click on event to view/edit
        eventClick: function(info) {
            const schedule = info.event.extendedProps.scheduleData;
            openEditModal(schedule);
        },
        
        // Enhanced event content customization
        eventContent: function(arg) {
            let wrapper = document.createElement('div');
            wrapper.classList.add('fc-event-main-custom');
            wrapper.style.padding = '6px 8px';
            wrapper.style.fontSize = '0.7rem';
            wrapper.style.lineHeight = '1.4';
            wrapper.style.cursor = 'pointer';
            wrapper.style.display = 'flex';
            wrapper.style.flexDirection = 'column';
            wrapper.style.gap = '3px';
            
            // Format time range with clock icon
            let timeStr = arg.timeText || '';
            if (timeStr) {
                let timeDiv = document.createElement('div');
                timeDiv.style.fontSize = '0.7rem'; // Increased from 0.65rem
                timeDiv.style.fontWeight = '600'; // Increased from 500
                timeDiv.style.opacity = '0.98';
                timeDiv.style.letterSpacing = '0.01em';
                timeDiv.style.display = 'flex';
                timeDiv.style.alignItems = 'center';
                timeDiv.style.gap = '4px';
                timeDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg><span>${timeStr}</span>`;
                wrapper.appendChild(timeDiv);
            }
            
            // Event title with service icon
            let titleDiv = document.createElement('div');
            titleDiv.style.fontSize = '0.78rem'; // Increased from 0.72rem
            titleDiv.style.fontWeight = '700'; // Increased from 600
            titleDiv.style.lineHeight = '1.3';
            titleDiv.style.display = 'flex';
            titleDiv.style.alignItems = 'center';
            titleDiv.style.gap = '4px';
            titleDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg><span>${arg.event.title}</span>`;
            wrapper.appendChild(titleDiv);
            
            // Add location with icon if available
            const venueName = arg.event.extendedProps.scheduleData?.venue?.name;
            const locationText = venueName || arg.event.extendedProps.scheduleData?.location || arg.event.extendedProps.location;
            
            if (locationText && locationText.trim() !== '') {
                let locationDiv = document.createElement('div');
                locationDiv.style.fontSize = '0.73rem'; // Increased from 0.68rem
                locationDiv.style.opacity = '0.95';
                locationDiv.style.fontWeight = '500'; // Increased from 400
                locationDiv.style.lineHeight = '1.3';
                locationDiv.style.display = 'flex';
                locationDiv.style.alignItems = 'center';
                locationDiv.style.gap = '4px';
                locationDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>${locationText}</span>`;
                wrapper.appendChild(locationDiv);
            }
            
            // Add priest with icon if available
            const scheduleData = arg.event.extendedProps.scheduleData || {};
            const internalPriest = scheduleData.priest?.name;
            const externalPriest = scheduleData.external_priest_name;
            const presiderName = internalPriest || externalPriest;
            if (presiderName) {
                let priestDiv = document.createElement('div');
                priestDiv.style.fontSize = '0.73rem'; // Increased from 0.68rem
                priestDiv.style.opacity = '0.95';
                priestDiv.style.fontWeight = '600'; // Increased from 500
                priestDiv.style.lineHeight = '1.3';
                priestDiv.style.display = 'flex';
                priestDiv.style.alignItems = 'center';
                priestDiv.style.gap = '4px';
                const externalBadge = internalPriest ? '' : ' <span style="font-size: 0.68rem; opacity: 0.85;">(External)</span>';
                priestDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg><span>${presiderName}${externalBadge}</span>`;
                wrapper.appendChild(priestDiv);
            }
            
            return { domNodes: [wrapper] };
        },
        
        // Styling
        themeSystem: 'standard',
        height: 'auto',
        
        // Event hover with smart positioning
        eventMouseEnter: function(info) {
            const tooltip = createTooltip(info.event);
            document.body.appendChild(tooltip);
            
            // Store reference for cleanup
            info.el.tooltipElement = tooltip;
            
            // Get positions
            const rect = info.el.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceLeft = rect.left;
            
            // Position to LEFT if in bottom rows, otherwise BELOW
            if (spaceBelow < 120) {
                // Position to the LEFT
                tooltip.style.position = 'fixed';
                tooltip.style.right = (window.innerWidth - rect.left + 10) + 'px';
                tooltip.style.top = (rect.top) + 'px';
                tooltip.style.left = 'auto';
            } else {
                // Position BELOW
                tooltip.style.position = 'fixed';
                tooltip.style.left = (rect.left) + 'px';
                tooltip.style.top = (rect.bottom + 5) + 'px';
            }
        },
        
        eventMouseLeave: function(info) {
            if (info.el.tooltipElement) {
                info.el.tooltipElement.remove();
                delete info.el.tooltipElement;
            }
        }
        ,
        // Called whenever the view changes (month/week/list). If the current
        // visible range has no events, show a helpful fallback panel with
        // upcoming events so the user isn't met with a blank "No events to display" box.
        datesSet: function(viewInfo) {
            try {
                // Force align all events to the top - use multiple attempts
                const alignEvents = () => {
                    const eventHarnesses = document.querySelectorAll('.fc-daygrid-event-harness');
                    eventHarnesses.forEach(harness => {
                        harness.style.setProperty('top', '0px', 'important');
                        harness.style.setProperty('position', 'relative', 'important');
                        harness.style.setProperty('inset', 'auto', 'important');
                        harness.style.setProperty('left', 'auto', 'important');
                        harness.style.setProperty('right', 'auto', 'important');
                    });
                };
                
                // Run immediately
                alignEvents();
                
                // Run again after short delays to ensure it sticks
                setTimeout(alignEvents, 10);
                setTimeout(alignEvents, 50);
                setTimeout(alignEvents, 100);
                
                const view = viewInfo.view;
                const calendarStart = view.activeStart;
                const calendarEnd = view.activeEnd;

                // Count events within the current visible range
                const eventsInRange = calendar.getEvents().filter(ev => {
                    const evStart = ev.start;
                    // Some events may be all-day; ensure we compare date ranges
                    return evStart >= calendarStart && evStart < calendarEnd;
                });

                // Remove existing fallback if any
                const existing = calendarEl.querySelector('.fc-empty-fallback');
                if (existing) existing.remove();

                if (eventsInRange.length === 0) {
                    // Build enhanced fallback panel
                    const panel = document.createElement('div');
                    panel.className = 'fc-empty-fallback';
                    panel.style.cssText = `
                        padding: 40px 32px;
                        text-align: center;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        border-radius: 20px;
                        margin: 16px;
                        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
                    `;

                    // Icon
                    const icon = document.createElement('div');
                    icon.style.cssText = 'font-size: 48px; margin-bottom: 16px;';
                    icon.innerHTML = '📅';
                    panel.appendChild(icon);

                    const title = document.createElement('div');
                    title.style.cssText = `
                        font-size: 24px;
                        font-weight: 800;
                        color: #ffffff;
                        margin-bottom: 8px;
                        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    `;
                    title.textContent = 'No events in this range';
                    panel.appendChild(title);

                    const subtitle = document.createElement('div');
                    subtitle.style.cssText = `
                        color: rgba(255, 255, 255, 0.9);
                        margin-bottom: 24px;
                        font-size: 16px;
                        font-weight: 500;
                    `;
                    subtitle.textContent = 'Here are the next upcoming events:';
                    panel.appendChild(subtitle);

                    // Show up to 5 upcoming events from the full events list
                    const upcoming = calendar.getEvents().filter(e => e.start >= new Date()).sort((a,b)=>a.start-b.start).slice(0,5);
                    if (upcoming.length === 0) {
                        const none = document.createElement('div');
                        none.style.cssText = `
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 15px;
                            padding: 16px;
                            background: rgba(255, 255, 255, 0.15);
                            border-radius: 12px;
                            backdrop-filter: blur(10px);
                        `;
                        none.innerHTML = '✨ No upcoming events available.';
                        panel.appendChild(none);
                    } else {
                        const list = document.createElement('ul');
                        list.style.cssText = `
                            list-style: none;
                            padding: 0;
                            margin: 0;
                            max-width: 600px;
                            margin: 0 auto;
                        `;
                        upcoming.forEach((ev, index) => {
                            const li = document.createElement('li');
                            li.style.cssText = `
                                padding: 16px 20px;
                                margin: 12px 0;
                                background: rgba(255, 255, 255, 0.95);
                                border-radius: 16px;
                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                text-align: left;
                                transition: all 0.3s ease;
                                cursor: pointer;
                                backdrop-filter: blur(10px);
                                border: 2px solid rgba(255, 255, 255, 0.3);
                            `;
                            li.onmouseover = function() {
                                this.style.transform = 'translateY(-2px)';
                                this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.15)';
                            };
                            li.onmouseout = function() {
                                this.style.transform = 'translateY(0)';
                                this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
                            };
                            
                            const eventColor = ev.backgroundColor || '#8B5CF6';
                            const dateTime = ev.start.toLocaleString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                            
                            const presiderName = ev.extendedProps.scheduleData?.priest?.name || ev.extendedProps.scheduleData?.external_priest_name;
                            const presiderBadge = ev.extendedProps.scheduleData?.priest ? '' : (presiderName ? ' (External)' : '');
                            li.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="
                                        width: 48px;
                                        height: 48px;
                                        background: ${eventColor};
                                        border-radius: 12px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-size: 20px;
                                        flex-shrink: 0;
                                        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
                                    ">
                                        ${index === 0 ? '🔔' : '📌'}
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="
                                            font-weight: 700;
                                            font-size: 16px;
                                            color: #1f2937;
                                            margin-bottom: 4px;
                                        ">${dateTime} — ${ev.title}</div>
                                        <div style="
                                            color: #6b7280;
                                            font-size: 14px;
                                            display: flex;
                                            align-items: center;
                                            gap: 6px;
                                        ">
                                            <span style="font-size: 12px;">📍</span>
                                            ${ev.extendedProps.scheduleData?.venue?.name || ev.extendedProps.location || 'Location TBA'}
                                        </div>
                                        ${presiderName ? `<div style="color: #6b7280; font-size: 14px; display: flex; align-items: center; gap: 6px; margin-top: 6px;">
                                            <span style="font-size: 12px;">👤</span>
                                            Presider: ${presiderName}${presiderBadge}
                                        </div>` : ''}
                                    </div>
                                </div>
                            `;
                            list.appendChild(li);
                        });
                        panel.appendChild(list);
                    }

                    // Append fallback into calendar container below header
                    const innerWrap = calendarEl.querySelector('.fc-view-harness') || calendarEl;
                    innerWrap.insertBefore(panel, innerWrap.firstChild);
                }
            } catch (e) {
                console.error('datesSet handler error:', e);
            }
        }
    });

    console.log('Rendering calendar...');
    calendar.render();
    console.log('Calendar rendered successfully!');
    console.log('Calendar instance:', calendar);
    
    // Store calendar instance globally for access from modal functions
    window.calendarInstance = calendar;
};

function getEventColor(eventType) {
    const colors = {
        'institutional_mass': '#8B5CF6',     // Purple
        'non_institutional_mass': '#3B82F6', // Blue
        'mass': '#8B5CF6',           // Purple (legacy)
        'confession': '#3B82F6',      // Blue
        'adoration': '#F59E0B',       // Amber
        'retreat': '#10B981',         // Green
        'seminar': '#EF4444',         // Red
        'meeting': '#6366F1',         // Indigo
        'celebration': '#EC4899',     // Pink
        'other': '#6B7280'            // Gray
    };
    return colors[eventType] || colors['other'];
}

function createTooltip(event) {
    const tooltip = document.createElement('div');
    tooltip.className = 'fc-tooltip';
    tooltip.style.cssText = `
        position: fixed;
        z-index: 99999;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: 3px solid #ffffff;
        padding: 14px 18px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        min-width: 220px;
        max-width: 350px;
        pointer-events: none;
    `;
    
    // Get mass_subtype if available, otherwise use title
    let massType = event.extendedProps.scheduleData?.mass_subtype || event.title;
    
    // Format: remove underscores and capitalize
    massType = massType
        .replace(/_/g, ' ')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
    
    let content = '<div style="color: #ffffff; text-align: center; font-size: 16px; font-weight: 700; line-height: 1.4;">' + massType + '</div>';

    // Presider line (internal or external)
    const presiderName = event.extendedProps.scheduleData?.priest?.name || event.extendedProps.scheduleData?.external_priest_name;
    if (presiderName) {
        content += '<div style="margin-top: 8px; color: #fff; text-align: center; font-size: 13px; font-weight: 600;">Presider: ' + presiderName + (event.extendedProps.scheduleData?.priest ? '' : ' (External)') + '</div>';
    }
    
    tooltip.innerHTML = content;
    return tooltip;
}

// Public calendar initialization (read-only for guests)
window.initPublicCalendar = function(schedules) {
    const calendarEl = document.getElementById('publiccalendar');
    
    if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
    }

    // Convert schedules to FullCalendar events
    const events = schedules.map(schedule => {
        // Normalize date and times safely
        const dateStr = normalizeDateStr(schedule.schedule_date);
        const startTime = normalizeTimeStr(schedule.start_time);
        const endTime = schedule.end_time ? normalizeTimeStr(schedule.end_time) : null;
        
        return {
            id: schedule.schedule_id,
            title: schedule.title,
            start: `${dateStr}T${startTime}`,
            end: endTime ? `${dateStr}T${endTime}` : null,
            description: schedule.description,
            location: schedule.location,
            eventType: schedule.event_type,
            backgroundColor: getEventColor(schedule.event_type),
            borderColor: getEventColor(schedule.event_type),
            extendedProps: {
                description: schedule.description,
                location: schedule.location,
                eventType: schedule.event_type,
                scheduleData: schedule
            }
        };
    });
    
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: events,
        editable: false,
        selectable: false,
        dayMaxEvents: 3,
        weekends: true,
        eventDisplay: 'block',
        displayEventTime: true,
        displayEventEnd: false,
        fixedWeekCount: false, // Don't force 6 weeks to be shown
        showNonCurrentDates: false, // Hide dates from other months
        
        // Click on event to view details
        eventClick: function(info) {
            showPublicEventModal(info.event.extendedProps.scheduleData);
        },
        
        // Enhanced event content customization - matching staff page
        eventContent: function(arg) {
            let wrapper = document.createElement('div');
            wrapper.classList.add('fc-event-main-custom');
            wrapper.style.padding = '3px 5px';
            wrapper.style.fontSize = '0.75rem';
            wrapper.style.lineHeight = '1.3';
            
            // Event time and title
            let eventText = document.createElement('div');
            eventText.style.fontWeight = '600';
            eventText.style.marginBottom = '2px';
            
            let timeStr = arg.timeText || '';
            eventText.innerHTML = `<strong style="font-size: 0.8rem;">${timeStr}</strong> ${arg.event.title}`;
            
            wrapper.appendChild(eventText);
            
            // Add location if available - Check venue relationship first, then location field
            const venueName = arg.event.extendedProps.scheduleData?.venue?.name;
            const locationText = venueName || arg.event.extendedProps.scheduleData?.location || arg.event.extendedProps.location;
            
            if (locationText && locationText.trim() !== '') {
                let locationDiv = document.createElement('div');
                locationDiv.style.fontSize = '0.7rem';
                locationDiv.style.opacity = '0.9';
                locationDiv.style.marginTop = '2px';
                locationDiv.innerHTML = `📍 ${locationText}`;
                wrapper.appendChild(locationDiv);
            }
            
            // Add priest if available
            const scheduleData = arg.event.extendedProps.scheduleData || {};
            const internalPriest = scheduleData.priest?.name;
            const externalPriest = scheduleData.external_priest_name;
            const presiderName = internalPriest || externalPriest;
            if (presiderName) {
                let priestDiv = document.createElement('div');
                priestDiv.style.fontSize = '0.7rem';
                priestDiv.style.opacity = '0.9';
                priestDiv.style.fontWeight = '600';
                priestDiv.style.marginTop = '2px';
                priestDiv.innerHTML = `� ${presiderName}${internalPriest ? '' : ' (External)'}`;
                wrapper.appendChild(priestDiv);
            }
            
            return { domNodes: [wrapper] };
        },
        
        themeSystem: 'standard',
        height: 'auto',
        
        // Add hover tooltips for better UX
        eventMouseEnter: function(info) {
            info.el.style.opacity = '0.9';
            info.el.style.transform = 'translateY(-1px)';
            info.el.style.cursor = 'pointer';
        },
        
        eventMouseLeave: function(info) {
            info.el.style.opacity = '1';
            info.el.style.transform = 'translateY(0)';
        }
    });

    calendar.render();
    window.publicCalendarInstance = calendar;
    
    // Return calendar instance for filtering
    return calendar;
};

// Export for use in blade templates
window.getEventColor = getEventColor;

// Export FullCalendar modules for use in blade templates
window.Calendar = Calendar;
window.dayGridPlugin = dayGridPlugin;
window.timeGridPlugin = timeGridPlugin;
window.listPlugin = listPlugin;
window.interactionPlugin = interactionPlugin;

// Programmatically navigate the public calendar to a given date (YYYY-MM-DD or Date)
window.publicCalendarSetDate = function(dateStrOrObj) {
    if (!window.publicCalendarInstance) return;
    try {
        window.publicCalendarInstance.gotoDate(dateStrOrObj);
    } catch (e) {
        const d = new Date(dateStrOrObj);
        if (!isNaN(d)) {
            window.publicCalendarInstance.gotoDate(d);
        }
    }
};
