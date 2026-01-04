import React, { useState, useRef, useEffect } from 'react';
import { ChevronDown } from 'lucide-react';
import './CustomDropdown.css';

const CustomDropdown = ({ label, options, value, onChange, placeholder = 'Select...' }) => {
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);

    const selectedOption = options.find(opt => opt.value === value);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = (optionValue) => {
        onChange(optionValue);
        setIsOpen(false);
    };

    return (
        <div className="custom-dropdown-container" ref={dropdownRef}>
            {label && <label className="dropdown-label">{label}</label>}
            <div
                className={`dropdown-trigger ${isOpen ? 'active' : ''}`}
                onClick={() => setIsOpen(!isOpen)}
            >
                <span className={!selectedOption ? 'placeholder' : ''}>
                    {selectedOption ? selectedOption.label : placeholder}
                </span>
                <ChevronDown size={18} className={`chevron-icon ${isOpen ? 'rotate' : ''}`} />
            </div>

            {isOpen && (
                <div className="dropdown-menu">
                    {options.map((option) => (
                        <div
                            key={option.value}
                            className={`dropdown-item ${value === option.value ? 'selected' : ''}`}
                            onClick={() => handleSelect(option.value)}
                        >
                            {option.label}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default CustomDropdown;
