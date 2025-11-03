import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

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
        // Extract just the date part (YYYY-MM-DD) from schedule_date
        const dateOnly = schedule.schedule_date.split('T')[0];
        
        // Extract just the time part (HH:MM:SS or HH:MM) from start_time and end_time
        const startTimeOnly = schedule.start_time.split(' ')[0] || schedule.start_time;
        const endTimeOnly = schedule.end_time ? (schedule.end_time.split(' ')[0] || schedule.end_time) : null;
        
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
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: events,
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: false, // Show all events in the cell
        weekends: true,
        eventDisplay: 'block', // Display events as blocks
        displayEventTime: true, // Show time in events
        displayEventEnd: false,
        
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
            wrapper.style.padding = '2px 4px';
            wrapper.style.fontSize = '0.75rem';
            wrapper.style.lineHeight = '1.2';
            
            // Create time and title text
            let eventText = document.createElement('div');
            eventText.style.fontWeight = '500';
            
            // Format time
            let timeStr = arg.timeText || '';
            
            // Add event content with time and title
            eventText.innerHTML = `<strong>${timeStr}</strong> ${arg.event.title}`;
            wrapper.appendChild(eventText);
            
            // Add location if available
            if (arg.event.extendedProps.location) {
                let locationDiv = document.createElement('div');
                locationDiv.style.fontSize = '0.7rem';
                locationDiv.style.opacity = '0.9';
                locationDiv.innerHTML = `📍 ${arg.event.extendedProps.location}`;
                wrapper.appendChild(locationDiv);
            }
            
            // Add priest if available
            if (arg.event.extendedProps.scheduleData && arg.event.extendedProps.scheduleData.priest) {
                let priestDiv = document.createElement('div');
                priestDiv.style.fontSize = '0.7rem';
                priestDiv.style.opacity = '0.9';
                priestDiv.style.fontWeight = '600';
                priestDiv.innerHTML = `👨‍⚕️ ${arg.event.extendedProps.scheduleData.priest.name}`;
                wrapper.appendChild(priestDiv);
            }
            
            return { domNodes: [wrapper] };
        },
        
        // Styling
        themeSystem: 'standard',
        height: 'auto',
        
        // Event hover
        eventMouseEnter: function(info) {
            const tooltip = createTooltip(info.event);
            info.el.appendChild(tooltip);
        },
        
        eventMouseLeave: function(info) {
            const tooltip = info.el.querySelector('.fc-tooltip');
            if (tooltip) {
                tooltip.remove();
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
        'mass': '#8B5CF6',           // Purple
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
        position: absolute;
        z-index: 10000;
        background: white;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        min-width: 200px;
        top: 100%;
        left: 0;
        margin-top: 5px;
    `;
    
    let content = `<strong>${event.title}</strong><br>`;
    if (event.extendedProps.location) {
        content += `📍 ${event.extendedProps.location}<br>`;
    }
    if (event.extendedProps.description) {
        content += `${event.extendedProps.description.substring(0, 100)}...`;
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
        // Extract date from schedule_date (handle both string and object formats)
        let dateStr = schedule.schedule_date;
        if (typeof dateStr === 'object' || dateStr.includes('T')) {
            // It's a datetime object or ISO string, extract just the date part
            dateStr = dateStr.split('T')[0];
        }
        
        return {
            id: schedule.schedule_id,
            title: schedule.title,
            start: `${dateStr}T${schedule.start_time}`,
            end: schedule.end_time ? `${dateStr}T${schedule.end_time}` : null,
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
            
            // Add location if available
            if (arg.event.extendedProps.location) {
                let locationDiv = document.createElement('div');
                locationDiv.style.fontSize = '0.7rem';
                locationDiv.style.opacity = '0.9';
                locationDiv.style.marginTop = '2px';
                locationDiv.innerHTML = `📍 ${arg.event.extendedProps.location}`;
                wrapper.appendChild(locationDiv);
            }
            
            // Add priest if available
            if (arg.event.extendedProps.scheduleData && arg.event.extendedProps.scheduleData.priest) {
                let priestDiv = document.createElement('div');
                priestDiv.style.fontSize = '0.7rem';
                priestDiv.style.opacity = '0.9';
                priestDiv.style.fontWeight = '600';
                priestDiv.style.marginTop = '2px';
                priestDiv.innerHTML = `👨‍⚕️ ${arg.event.extendedProps.scheduleData.priest.name}`;
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
};

// Export for use in blade templates
window.getEventColor = getEventColor;

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
